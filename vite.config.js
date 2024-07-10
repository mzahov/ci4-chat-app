import { defineConfig } from "vite";
import { resolve } from "path";

export default defineConfig(() => {
  return {
    build: {
      manifest: false,
      minify: true,
      copyPublicDir: false,
      sourcemap: true,
      rollupOptions: {
        input: {
          app: resolve(__dirname, "./resources/css/app.scss"),
          main: resolve(__dirname, "./resources/js/main.js"),
        },
        output: {
          assetFileNames: "[name][extname]",
          entryFileNames: "[name].js",
          chunkFileNames: "[name].js",
        },
      },
      outDir: resolve(__dirname, "public/assets"),
    },
    publicDir: resolve(__dirname, "public"),
  };
});