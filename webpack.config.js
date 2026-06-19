const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
require('dotenv').config();

module.exports = {
  mode: process.env.NODE_ENV || 'development',

  entry: {
    app: './assets/js/app.js',
    styles: './assets/css/app.css',
  },

  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'dist'),
    publicPath: '/wp-content/themes/matrix-starter/dist/',
  },

  module: {
    rules: [
      {
        test: /\.css$/,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          'postcss-loader',
        ],
      },
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: { presets: ['@babel/preset-env'] },
        },
      },
    ],
  },

  plugins: [
    new MiniCssExtractPlugin({ filename: '[name].css' }),
  ],

  optimization: {
    minimize: process.env.NODE_ENV === 'production',
    minimizer: [
      `...`, // <-- Keeps default JS minimizer (Terser)
      new CssMinimizerPlugin(), // <-- Minify CSS
    ],
  },

  devServer: {
    static: { directory: path.join(__dirname), watch: true },
    compress: true,
    port: process.env.DEV_SERVER_PORT || 3000,
    proxy: {
      '/': {
        target: process.env.WP_HOME || 'http://localhost:10054',
        changeOrigin: true,
        secure: false,
      },
    },
    hot: false,
    devMiddleware: { writeToDisk: true },
    watchFiles: ['assets/**/*.{js,css}'],
    client: {
      overlay: {
        errors: true,
        warnings: false,
        // Third-party CDN scripts (Alpine, reCAPTCHA, Turnstile, Slick, etc.) throw
        // cross-origin errors that browsers report only as "Script error." — noisy and
        // unactionable. Real theme bundle errors still include filename/stack and show.
        runtimeErrors: (error) => {
          if (!error || error.message === 'Script error.') {
            return false;
          }
          // Benign View Transitions API rejection — a transition that gets
          // superseded/interrupted rejects with AbortError "Transition was
          // skipped". It's expected and not actionable, so don't surface it.
          const message = (error && error.message) || '';
          if (error.name === 'AbortError' || message.indexOf('Transition was skipped') !== -1) {
            return false;
          }
          if (!error.filename && !error.stack) {
            return false;
          }
          return true;
        },
      },
    },
  },
};
