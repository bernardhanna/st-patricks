#!/usr/bin/env node

const fs = require("node:fs/promises");
const path = require("node:path");
const { TailwindConverter } = require("css-to-tailwindcss");

async function run() {
  const [, , inputArg, outputArg, configArg] = process.argv;

  if (!inputArg || !outputArg) {
    console.error(
      "Usage: node scripts/convert-css-to-tailwindcss.cjs <input.css> <output.css> [tailwind.config.js]"
    );
    process.exit(1);
  }

  const inputPath = path.resolve(process.cwd(), inputArg);
  const outputPath = path.resolve(process.cwd(), outputArg);
  const configPath = path.resolve(
    process.cwd(),
    configArg || process.env.TAILWIND_CONFIG || "tailwind.config.js"
  );

  let tailwindConfig = {};
  try {
    // Tailwind config in this repo is CommonJS, so require is expected here.
    // eslint-disable-next-line global-require, import/no-dynamic-require
    tailwindConfig = require(configPath);
  } catch (error) {
    console.warn(
      `[css-to-tailwindcss] Could not load config at ${configPath}, using defaults.`
    );
  }

  const inputCss = await fs.readFile(inputPath, "utf8");
  const converter = new TailwindConverter({ tailwindConfig });
  const { convertedRoot } = await converter.convertCSS(inputCss);

  await fs.mkdir(path.dirname(outputPath), { recursive: true });
  await fs.writeFile(outputPath, convertedRoot.toString(), "utf8");

  console.log(
    `[css-to-tailwindcss] Converted ${path.relative(
      process.cwd(),
      inputPath
    )} -> ${path.relative(process.cwd(), outputPath)}`
  );
}

run().catch((error) => {
  console.error("[css-to-tailwindcss] Conversion failed:", error);
  process.exit(1);
});
