// Thin wrapper around the global MathJax 4 engine loaded in index.html.
// MathJax renders <math> markup that components inject via v-html; because the
// markup appears only when its view/slide is shown, typesetting has to happen
// on demand rather than at page load.

// index.html sets window.MathJax to a plain config object *before* the real
// engine (/mathjax/mml-svg.js, loaded async) has run - at that point
// startup.promise doesn't exist yet, it isn't just unresolved. Falling back
// to an already-resolved promise in that gap (instead of waiting for the
// real one to appear) let callers reach typesetPromise before the engine had
// actually finished initializing, so whichever view got typeset first after
// a reload could throw "Math input error" while later views, by then given
// enough time for the script to load, worked fine. Poll until the real
// promise shows up, then await it, and cache the result for later calls.
let readyPromise = null;

function engineReady() {
  if (!readyPromise) {
    readyPromise = new Promise((resolve) => {
      const check = () => {
        if (window.MathJax?.startup?.promise) window.MathJax.startup.promise.then(resolve);
        else setTimeout(check, 20);
      };
      check();
    });
  }
  return readyPromise;
}

// Typeset the MathML inside `el` (an Element). Safe to call before the engine
// has finished loading and safe to call repeatedly on the same element.
// Non-element nodes are ignored: a component with several root nodes exposes
// its `$el` as a text anchor, and MathJax's DOM adaptor blows up
// ("getElementsByTagName is not a function") on anything that isn't an Element.
export async function typesetMath(el) {
  if (!el || el.nodeType !== 1) return;
  await engineReady();
  if (!window.MathJax?.typesetPromise) return;
  try {
    window.MathJax.typesetClear([el]);
    await window.MathJax.typesetPromise([el]);
  } catch (err) {
    console.error("MathJax typeset failed", err);
  }
}
