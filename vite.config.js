import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";

// BiVeS merge backend (PHP). Defaults to the production host; point at the local
// docker-compose stack for offline development:
//   BIVES_PROXY=http://localhost:8088 npm run dev
const bivesProxy =
  process.env.BIVES_PROXY ||
  "https://merge-proto.bio.informatik.uni-rostock.de";

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  build: {
    emptyOutDir: true,
  },
  server: {
    proxy: {
      "/bives": {
        target: bivesProxy,
        changeOrigin: true,
        secure: true,
      },
    },
  },
});
