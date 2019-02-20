# javascript

Javascript is code that is run in the browser of the client.

For every page (module), the main javascript file is found under `/src/Modules/<modulename>/...js`.
All other javascript (should) be found under `/client`.

## imports
Javascript has different ways of using (importing) code from other files.
We use [`npm`](https://docs.npmjs.com/), the standard package manager for javascript.

All used packages are listed under /client/package.json.
For using a package, we use the `import` command at beginnings of files.
During build of the webpage the imports are resolved and only what is necessary is sent to the user.

To enable small bits of javacode somewhere (inline), there is `/client/src/globals.js`.
There a bunch of functions are made avaiable globally.

## vue.js

The modern way of designing front end pages is [`vue.js`](https://vuejs.org/v2/guide/).
We try to use more of it do get more order into javascript files.
The big advantage is, that html, javascript and css are clearly separated.
One example is `/client/src/components/Topbar/Login.vue`.
Before diving into happy `vue.js`-hacking you probably should read a bit in the vue [documentation](https://vuejs.org/v2/guide/).


