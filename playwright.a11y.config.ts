// playwright.a11y.config.ts
import { defineConfig } from "@playwright/test";
import * as dotenv from "dotenv";

dotenv.config({ path: ".env" });

export default defineConfig({
  testDir: "tests/a11y",
  outputDir: "tests/pw-artifacts",
  reporter: [
    ["list"],
    ["html", { outputFolder: "tests/a11y-report", open: "never" }],
  ],
  use: {
    baseURL:
      process.env.BASE_URL || process.env.WP_HOME || "http://localhost:10034",
    trace: "on-first-retry",
  },
  projects: [{ name: "chromium", use: { browserName: "chromium" } }],
});
