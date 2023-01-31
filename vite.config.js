//import { fileURLToPath, URL } from "url";

import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";

//const path = require('path');

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  build: {
    base: "/vue-Model-Merger/",
    //base: "/",
    //outDir: "./sbml-merger/",
    //sourcemap: true,
    emptyOutDir: true,
    /*     rollupOptions: {
          output: {
            entryFileNames: "[name].js",
            chunkFileNames: "[name].js",
            assetFileNames: "[name].txt"
          }
        } */
  },
  /*   resolve: {
      alias: {
        //base: '/vue3-merger/',
        //root: path.resolve(__dirname, 'src'),
        //"@": fileURLToPath(new URL("./src", import.meta.url)),
        //vue: path.resolve('./node_modules/vue'),
        //'~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),
  
      },
    }, */
});