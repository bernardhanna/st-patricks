// tests/a11y/a11y.spec.ts
import { test, expect } from "@playwright/test";
import AxeBuilder from "@axe-core/playwright";
import * as fs from "fs";
import * as path from "path";

/**
 * Accessibility scan (axe-core / WCAG 2.1 A + AA).
 *
 * Modes:
 * - `npm run test:a11y`        scans DEFAULT_PATHS (homepage).
 * - `npm run test:a11y:full`   scans FULL_PATHS (key templates).
 * - `npm run test:a11y:site`   scans every URL in tests/a11y/urls.json
 *                              (run `npm run test:a11y:urls` first to build it).
 * - `A11Y_PATHS="/a,/b"`       override with a comma-separated list.
 *
 * Base URL comes from BASE_URL (e.g. BASE_URL=http://localhost:10034).
 */

const DEFAULT_PATHS = ["/"];

const FULL_PATHS = [
  "/",
  "/make-a-referral/",
  "/referrals/",
  "/contact-us/",
  "/about-us/",
];

const WCAG_TAGS = ["wcag2a", "wcag2aa", "wcag21a", "wcag21aa"];

const SETTLE_MS = Number(process.env.A11Y_SETTLE_MS || 600);

// Third-party embeds whose internal markup we don't control. We still audit our
// own <iframe> elements (titles etc.); these excludes only drop the violations
// that live *inside* the provider's cross-origin document.
const THIRD_PARTY_EMBEDS = [
  "youtube.com",
  "youtube-nocookie.com",
  "youtu.be",
  "facebook.com",
  "instagram.com",
  "twitter.com",
  "x.com",
  "vimeo.com",
  "player.vimeo.com",
  "spotify.com",
  "soundcloud.com",
  "mixcloud.com",
  "audioboom.com",
  "anchor.fm",
  "google.com/maps",
  "podbean.com",
  "buzzsprout.com",
];

function buildScanner(page: import("@playwright/test").Page): AxeBuilder {
  let builder = new AxeBuilder({ page }).withTags(WCAG_TAGS);
  for (const host of THIRD_PARTY_EMBEDS) {
    builder = builder.exclude(`iframe[src*="${host}"]`);
  }
  return builder;
}

function readSitePaths(): string[] {
  const file = path.join(__dirname, "urls.json");
  if (!fs.existsSync(file)) {
    throw new Error(
      `tests/a11y/urls.json not found. Run "npm run test:a11y:urls" first.`
    );
  }
  return JSON.parse(fs.readFileSync(file, "utf8"));
}

function listPaths(): string[] {
  if (process.env.A11Y_PATHS) {
    return process.env.A11Y_PATHS.split(",")
      .map((p) => p.trim())
      .filter(Boolean);
  }
  if (process.env.A11Y_SITE) return readSitePaths();
  if (process.env.A11Y_FULL) return FULL_PATHS;
  return DEFAULT_PATHS;
}

type RuleSummary = {
  id: string;
  impact: string;
  help: string;
  helpUrl: string;
  nodeCount: number;
  pageCount: number;
  examplePages: string[];
  exampleTarget: string;
};

if (process.env.A11Y_SITE) {
  // Full-site mode: one long-running test that aggregates violations by rule.
  const paths = listPaths();

  test("a11y: full site", async ({ page }) => {
    test.setTimeout(2 * 60 * 60 * 1000); // up to 2h for large sites

    const rules = new Map<string, RuleSummary>();
    const errored: string[] = [];
    let scanned = 0;

    for (const p of paths) {
      try {
        await page.goto(p, { waitUntil: "load", timeout: 30000 });
        await page.waitForTimeout(SETTLE_MS);
        const results = await buildScanner(page).analyze();

        for (const v of results.violations) {
          const entry =
            rules.get(v.id) ??
            ({
              id: v.id,
              impact: v.impact || "n/a",
              help: v.help,
              helpUrl: v.helpUrl,
              nodeCount: 0,
              pageCount: 0,
              examplePages: [],
              exampleTarget: v.nodes[0]?.target?.join(" ") || "",
            } as RuleSummary);

          entry.nodeCount += v.nodes.length;
          entry.pageCount += 1;
          if (entry.examplePages.length < 8) entry.examplePages.push(p);
          rules.set(v.id, entry);
        }
      } catch (err) {
        errored.push(`${p} :: ${(err as Error).message}`);
      }
      scanned += 1;
    }

    const summary = [...rules.values()].sort((a, b) => b.nodeCount - a.nodeCount);

    const report = {
      generatedAt: new Date().toISOString(),
      baseURL: test.info().project.use.baseURL,
      pagesScanned: scanned,
      pagesErrored: errored,
      ruleViolations: summary,
    };

    fs.writeFileSync(
      path.join(__dirname, "a11y-summary.json"),
      JSON.stringify(report, null, 2)
    );

    const lines: string[] = [
      ``,
      `==================== A11Y FULL-SITE SUMMARY ====================`,
      `Pages scanned: ${scanned}   Errored: ${errored.length}`,
      `Distinct rule violations: ${summary.length}`,
      `----------------------------------------------------------------`,
    ];
    for (const r of summary) {
      lines.push(
        `[${r.impact}] ${r.id} — ${r.help}`,
        `   pages: ${r.pageCount}  nodes: ${r.nodeCount}`,
        `   e.g.  ${r.examplePages.slice(0, 3).join(", ")}`,
        `   sel:  ${r.exampleTarget}`,
        `   doc:  ${r.helpUrl}`,
        ``
      );
    }
    if (errored.length) {
      lines.push(`Errored pages:`, ...errored.map((e) => `   ${e}`), ``);
    }
    console.log(lines.join("\n"));

    const blocking = summary.filter(
      (r) => r.impact === "serious" || r.impact === "critical"
    );

    expect(
      blocking,
      `${blocking.length} serious/critical a11y rule(s) across the site. See tests/a11y/a11y-summary.json`
    ).toEqual([]);
  });
} else {
  // Per-path mode.
  for (const p of listPaths()) {
    test(`a11y: ${p}`, async ({ page }, testInfo) => {
      await page.goto(p, { waitUntil: "load" });
      await page.waitForTimeout(1200);

      const results = await buildScanner(page).analyze();

      await testInfo.attach("axe-violations.json", {
        body: JSON.stringify(results.violations, null, 2),
        contentType: "application/json",
      });

      if (results.violations.length) {
        const lines: string[] = [`\nA11y violations for ${p}:`];
        for (const v of results.violations) {
          lines.push(`  [${v.impact}] ${v.id} — ${v.help}`);
          lines.push(`     ${v.helpUrl}`);
          for (const node of v.nodes) {
            lines.push(`     → ${node.target.join(" ")}`);
          }
        }
        console.log(lines.join("\n"));
      }

      const blocking = results.violations.filter(
        (v) => v.impact === "serious" || v.impact === "critical"
      );

      expect(
        blocking,
        `${blocking.length} serious/critical a11y violation(s) on ${p}`
      ).toEqual([]);
    });
  }
}
