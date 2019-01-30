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
window.Date = Date
