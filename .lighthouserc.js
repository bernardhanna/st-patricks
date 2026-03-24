// .lighthouserc.js
require('dotenv').config({ path: '.env' });   // ← loads .env into process.env

module.exports = {
  ci: {
    /** Collect stage */
    collect: {
      url: [process.env.BASE_URL || process.env.WP_HOME || 'http://localhost:8888'],
      numberOfRuns: 3,        // average over 3 runs
      settings: { preset: 'desktop' }
    },

    /** Assert / budget stage */
    assert: {
      assertions: {
        'cumulative-layout-shift': ['warn', { maxNumericValue: 0.5 }],
        'first-contentful-paint': ['warn', { maxNumericValue: 3000 }],
        'largest-contentful-paint': ['warn', { maxNumericValue: 7000 }]
      }
    },


    /** Upload stage — keep temporary storage for now */
    upload: {
      target: 'filesystem',
      outputDir: 'tests/lhci-report'   // anything under project root is fine
    }
  }
};
