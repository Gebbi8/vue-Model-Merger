import js from "@eslint/js";
import pluginVue from "eslint-plugin-vue";
import skipFormatting from "@vue/eslint-config-prettier/skip-formatting";
import globals from "globals";

export default [
  {
    ignores: ["dist/**", "public/**", "bives/**", "DiVil/**", "dev/**"],
  },

  js.configs.recommended,
  ...pluginVue.configs["flat/essential"],
  skipFormatting,

  {
    files: ["**/*.{js,mjs,cjs,vue}"],
    languageOptions: {
      ecmaVersion: "latest",
      sourceType: "module",
      globals: {
        ...globals.browser,
        MathJax: "readonly",
      },
    },
    rules: {
      // single-word component names (Merger) are used deliberately here
      "vue/multi-word-component-names": "off",
    },
  },

  {
    files: ["*.config.js", "scripts/**"],
    languageOptions: {
      globals: { ...globals.node },
    },
  },
];
