import { defineConfig, loadEnv } from "vite";
import vue from "@vitejs/plugin-vue";

// BiVeS merge backend (PHP wrappers) for the /bives/*.php dev proxy.
// Resolution order:
//   1. BIVES_PROXY in the process environment (CLI, or the docker compose `web` service)
//   2. BIVES_PROXY from a .env file
//   3. the production host
export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), "");
  const bivesProxy =
    process.env.BIVES_PROXY ||
    env.BIVES_PROXY ||
    "https://merge-proto.bio.informatik.uni-rostock.de";

  return {
    plugins: [vue()],
    build: {
      emptyOutDir: true,
    },
    server: {
      host: true,
      proxy: {
        "/bives": {
          target: bivesProxy,
          changeOrigin: true,
          secure: true,
        },
      },
    },
  };
});
