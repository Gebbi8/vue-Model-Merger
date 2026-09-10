import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  build: {
    emptyOutDir: true,
  },
  server: {
    proxy: {
      // BiVeS merge backend (PHP). In production the app is deployed
      // alongside these scripts on merge-proto; in dev we forward to it.
      "/bives": {
        target: "https://merge-proto.bio.informatik.uni-rostock.de",
        changeOrigin: true,
        secure: true,
      },
    },
  },
});
