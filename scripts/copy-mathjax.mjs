// Vendors the MathJax 4 SVG component into public/ so it can be served without
// a CDN. Runs from predev / prebuild; public/mathjax/ is git-ignored.
import { cpSync, existsSync, mkdirSync } from "node:fs";
import { dirname, resolve } from "node:path";
import { fileURLToPath } from "node:url";

const root = resolve(dirname(fileURLToPath(import.meta.url)), "..");
const pkg = resolve(root, "node_modules/mathjax");
const dest = resolve(root, "public/mathjax");

if (!existsSync(resolve(pkg, "mml-svg.js"))) {
  console.error("mathjax package not found - run `npm install` first");
  process.exit(1);
}

// The combined mml-svg component embeds the default font, so this one file is
// the whole engine - no separate font/output bundles to serve. Overwrite in
// place (dest may be a mount point under docker compose).
mkdirSync(dest, { recursive: true });
cpSync(resolve(pkg, "mml-svg.js"), resolve(dest, "mml-svg.js"));

console.log("vendored MathJax -> public/mathjax/mml-svg.js");
