const mix = require('laravel-mix');
const StyleLintPlugin = require('stylelint-webpack-plugin');
const SVGSpritemapPlugin = require('svg-spritemap-webpack-plugin');
const MinifyPlugin = require("babel-minify-webpack-plugin");
const nodeSass = require('node-sass');
const webpack = require('webpack');
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application, as well as bundling up your JS files.
 |
 */

mix.setPublicPath('dist/');

mix.js('src/js/main.js', 'site.js')
  .sass('src/sass/main.scss', 'site.css', { implementation: nodeSass })
  .version();

mix.sass('src/sass/admin.scss', 'admin.css', { implementation: nodeSass })
  .version();

mix.sass('it_showcase/sass/showcase.scss', 'showcase.css', { implementation: nodeSass });

mix.copy('src/js/lib/modernizr.min.js', 'dist/modernizr.min.js');

mix.options({
  cleanCss: {
    level: {
      1: {
        specialComments: 'none',
      },
    },
  },
  postCss: [
    require('postcss-discard-comments')({ removeAll: true }),
  ],
  transformToRequire: {
    image: 'xlink:href',
  },
});

mix.webpackConfig({
  output: {
    publicPath: '/themes/custom/uccollege/dist/',
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env'],
          },
        },
      },
    ],
  },
});

// run eslint and stylelint and create sourcemaps in development
if (process.env.NODE_ENV === 'development') {
  mix.options({
    sourcemaps: 'source-map',
  });

  mix.webpackConfig({
    plugins: [
      new StyleLintPlugin(),
    ],
    module: {
      rules: [
        {
          test: /\.js$/,
          exclude: /node_modules/,
          enforce: 'pre',
          use: [{
            loader: 'eslint-loader',
          }],
        }],
    },
  });
}

if (process.env.NODE_ENV === 'production') {
  mix.webpackConfig({
    plugins: [
      new MinifyPlugin(),
      new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/),
    ],
  });
}

// Set up the spritemap plugin
mix.webpackConfig({
  plugins: [
    new SVGSpritemapPlugin('src/img/svg/**/*.svg', {
      output: {
        filename: 'icons.svg',
        svg4everybody: true,
        svgo: {
          removeTitle: true,
          removeStyleElement: true,
          cleanupNumericValue: true,
        },
        chunk: {
          keep: true,
        },
      },
      sprite: {
        prefix: false,
      },
    }),
  ],
});

// Full API
// mix.js(src, output);
// mix.react(src, output); <-- Identical to mix.js(), but registers React Babel compilation.
// mix.preact(src, output); <-- Identical to mix.js(), but registers Preact compilation.
// mix.coffee(src, output); <-- Identical to mix.js(), but registers CoffeeScript compilation.
// mix.ts(src, output); <-- TypeScript support. Requires tsconfig.json to exist in the same folder as webpack.mix.js
// mix.extract(vendorLibs);
// mix.sass(src, output);
// mix.less(src, output);
// mix.stylus(src, output);
// mix.postCss(src, output, [require('postcss-some-plugin')()]);
// mix.browserSync('my-site.test');
// mix.combine(files, destination);
// mix.babel(files, destination); <-- Identical to mix.combine(), but also includes Babel compilation.
// mix.copy(from, to);
// mix.copyDirectory(fromDir, toDir);
// mix.minify(file);
// mix.sourceMaps(); // Enable sourcemaps
// mix.version(); // Enable versioning.
// mix.disableNotifications();
// mix.setPublicPath('path/to/public');
// mix.setResourceRoot('prefix/for/resource/locators');
// mix.autoload({}); <-- Will be passed to Webpack's ProvidePlugin.
// mix.webpackConfig({}); <-- Override webpack.config.js, without editing the file directly.
// mix.babelConfig({}); <-- Merge extra Babel configuration (plugins, etc.) with Mix's default.
// mix.then(function () {}) <-- Will be triggered each time Webpack finishes building.
// mix.dump(); <-- Dump the generated webpack config object t the console.
// mix.extend(name, handler) <-- Extend Mix's API with your own components.
// mix.options({
//   extractVueStyles: false, // Extract .vue component styling to file, rather than inline.
//   globalVueStyles: file, // Variables file to be imported in every component.
//   processCssUrls: true, // Process/optimize relative stylesheet url()'s. Set to false, if you don't want them touched.
//   purifyCss: false, // Remove unused CSS selectors.
//   terser: {}, // Terser-specific options. https://github.com/webpack-contrib/terser-webpack-plugin#options
//   postCss: [] // Post-CSS options: https://github.com/postcss/postcss/blob/master/docs/plugins.md
// });
