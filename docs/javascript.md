# javascript

Javascript is code that is run in the browser of the client.

For every page (module), the main javascript file is found under `/src/Modules/<modulename>/...js`. This is automatically sent to the client.
All other javascript (should) be found under `/client`.

## imports

Javascript has different ways of using (importing) code from other files.
We use [`npm`](https://docs.npmjs.com/), the standard package manager for javascript.

All used third-party-packages are listed under `/client/package.json`.
For using a package, we use the `import` command at beginnings of files.
During build of the webpage the imports are resolved (see webpack) and only what is necessary is sent to the user.

<!-- Some third-party-libraries are still part of the repository and are loaded  -->
To enable small bits of javacode somewhere (inline), there is `/client/src/globals.js`.
There a bunch of functions are made avaiable globally.
This is imported in the main module js-files via `import '@/core'` and `import '@/globals'`.

## webpack

The javascript files are not directly sent to the client but preprocessed by [webpack](https://webpack.js.org/concepts).
Webpack is configured via `/client/webpack.base.js`.
For example this includes the alias that translate `@` in imports into `/client/src`.

## vue.js

The modern way of designing front end pages is [`vue.js`](https://vuejs.org/v2/guide/).
We try to use more of it do get more order into javascript files.
The big advantage is, that html, javascript and css are clearly separated.
One example is `/client/src/components/Topbar/Login.vue`.
Before diving into happy `vue.js`-hacking you probably should read a bit in the vue [documentation](https://vuejs.org/v2/guide/).


