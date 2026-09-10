// Thin wrapper around the global MathJax 4 engine loaded in index.html.
// MathJax renders <math> markup that components inject via v-html; because the
// markup appears only when its view/slide is shown, typesetting has to happen
// on demand rather than at page load.

function engineReady() {
  return window.MathJax?.startup?.promise ?? Promise.resolve();
}

// Typeset the MathML inside `el` (an Element). Safe to call before the engine
// has finished loading and safe to call repeatedly on the same element.
export async function typesetMath(el) {
  if (!el || !window.MathJax) return;
  await engineReady();
  if (!window.MathJax.typesetPromise) return;
  try {
    window.MathJax.typesetClear([el]);
    await window.MathJax.typesetPromise([el]);
  } catch (err) {
    console.error("MathJax typeset failed", err);
  }
}
