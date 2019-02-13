const JSDOMGlobal = require('jsdom-global')
const { LocalStorage } = require('node-localstorage')

const html = `
  <!doctype html>
  <html>
      <body></body>
  </html>
`.trim()

JSDOMGlobal(html, { url: 'https://foodsharing.de' })

window.localStorage = new LocalStorage('/tmp')
// fix for vue-loader 15.6.2 - See  https://github.com/vuejs/vue-test-utils/issues/936
window.Date = Date
