const mix = require('laravel-mix');

const themePath = 'client';
const jsPath = `${themePath}/src/js/`;
const destPath = `${themePath}/dist/`;

const SRC = {
  js: jsPath + 'main.js',
  repeatfield: jsPath + 'repeatfield.js'
};

const DEST = {
  css: destPath,
  js: destPath
};

mix.setPublicPath(__dirname);

mix.options({
  processCssUrls: false,
});

mix.js(SRC.js, DEST.js);
mix.js(SRC.repeatfield, DEST.js);