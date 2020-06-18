# University of Chicago, The College: Frontend Theme Docs

Please note that `lando start` or `lando rebuild` will run `npm install && npm run dev` when the lando docker instance is initiated

The project backend can be updated (composer and Drupal config) with:
```bash
lando update
```
The project's frontend can be updated with:
```bash
lando update-fe
```

## Local Dependency Setup

Requires [npm](https://nodejs.org/en/download/)

Once NPM in installed, from within the `docroot/themes/custom/uccollege/` folder, run:

```bash
lando npm install
```

## Asset Compilation

Compilation and bundling of assets like JavaScript and Sass are handled with [Laravel Mix](https://laravel.com/docs/5.5/mix) and [Webpack](https://webpack.js.org/).

In development, you can have Mix watch for changes to Sass and JavaScript files via this command, run from within `/docroot/themes/custom/uccollege`. (Note: All frontend dev should usually be done within this folder):

```bash
lando npm run watch
```

You can also process sass and js manually via:

```bash
lando npm run dev
```
or

```bash
lando npm run production
```

Both will save JavaScript, CSS and sourcemap files to the dist folder. The production task will also minify assets.

The "hot" task will launch the Webpack server and serve CSS and JavaScript assets from there, hot-reloading as changes are made. This requires the html pages to serve those assets from the Webpack server's IP, which hasn't been set up yet.  

# Project Guidelines

## Sass
This project is using a hybrid of [BEM](https://en.bem.info/), [SMACSS](https://smacss.com/) and [CSS Namespacing](https://csswizardry.com/2015/03/more-transparent-ui-code-with-namespaces/#the-namespaces).

Sass files are located in `/docroot/themes/custom/uccollege/src/sass` with a main entry point of main.scss. All other files must be brought into the project by this file (or a file it's imported).

Sass folder structure:
```
/sass
    /components
    /global
    /libs
```

**/components:** Components are reusable modules that represent specific layouts, functionality, etc. Component clases are prefixed with `c-`. For example, a reusable sidebar might have a class of `c-sidebar`.

Also note that _only_ components with no other associated files (ie, twig, vue, etc) should go in this folder. Otherwise they should be grouped with their related files in `docroot/themes/custom/uccollege/components`.

**/libs:** The libs folder is for 3rd-party vendor files. Styles that can be included via NPM should be (like Foundation). Any others should go in the /libs folder, and imported by main.scss.

**/global:** Contains styles that can be applied globally, regardless of object or component.

Global files:

* _base.scss: Styles for all base HTML elements (h1, p, a, etc). This also serves as a quasi-reset, and should not usually contain class declarations.
* _layout.scss: Styles specifically related to layout elements (not grid-related). Modifiers to things like height, padding, etc should go here.
* _settings.scss: This file is mostly used for declaring SASS variables for things like global colors, pixel values, etc.
* _skin.scss: Global modifiers for things like color, shadow, borders, etc. These do not need special prefixes (although .s- would work), and can be applied directly to elements to modify appearance. Example: `<button class="o-button no-border">`. Note that these should be used in instances of one-off minor variations. A component should define it's specific appearance for most situations.
* _typography: Styles related to fonts and typography. Not all site typography needs defined here. But all `font-face` and font-related includes and mixins should be.
* _utilities: General helper classes, functions and mixins.

### Sass Linting
Sass linting is handled by [Stylelint](https://stylelint.io/user-guide/rules/) and automatically runs in the Development environment. Stylelint rule configs can be found within the `.stylelintrc` file. You can also disable certain rules via comments in Sass files: `/* stylelint-disable-line declaration-no-important */`.

## JavaScript
* This project uses ES6 for all JavaScript, processed by Babel.
* All components requiring interactivity, state management, etc are built as [single-file Vue components](https://vuejs.org/v2/guide/single-file-components.html).

## State Management
Global state management is handled with [Vuex](https://vuex.vuejs.org) and is currently used by the Masthead and Navigation components. 

## Component Creation
To easily create new Vue components and supporting files, run this from the theme folder:

```bash
./components/new-component.sh
```

This will clone the `docroot/themes/custom/uccollege/components/starter` folder, and replace all occurrences of "starter" with the component name you chose. You'll also need to manually declare that component in `docroot/themes/custom/uccollege/src/js/main.js`.

### Bundling
Javascript imports and requires are being processed by Webpack. ES6 conversion is handled by Babel.

### JS Linting
Javascript linting is done with `eslint`, based on the [airbnb](https://github.com/airbnb/javascript) linting rules. You can disable certain rules with comments like: `/* eslint-disable rule-here */`.

## Component Naming and Folder Conventions
All Vue components should be located in a subfolder of the `docroot/themes/custom/uccollege/components` folder. All files related to that component should also be there. 

Component folder structure, using the Sample component as an example:

```
/components
	/sample
		/_c-sample.scss
		/README.md
		/sample.twig
		/Sample.vue
```

Note how each file uses the same name. Not every file is required, as there may be instances where a Twig file isn't needed, etc. 
