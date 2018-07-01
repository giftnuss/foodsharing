const JSDOMGlobal = require('jsdom-global')
const { LocalStorage } = require('node-localstorage')

const html = `
  <!doctype html>
  <html>
      <body>
          <div id="main"></div>
          <noscript>          
            <div id="nojs"></div>
          </noscript>
          <div class="pulse-msg ui-shadow ui-corner-all" id="pulse-error" style="display:none;"></div>
          <div class="pulse-msg ui-shadow ui-corner-all" id="pulse-info" style="display:none;"></div>
          <div class="pulse-msg ui-shadow ui-corner-all" id="pulse-success" style="display:none;"></div>    
      </body>
  </html>
`.trim()

JSDOMGlobal(html, { url: 'https://foodsharing.de' })

window.localStorage = new LocalStorage('/tmp')
