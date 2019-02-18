/* eslint-env mocha */

import assert from 'assert'
import { resetModules, later } from '>/utils'

describe('vMap', () => {
  beforeEach(() => {
    const options = {
      center: [50.89, 10.13],
      searchpanel: 'searchpanel',
      zoom: 13,
      markers: [
        {
          lat: 50.89,
          lng: 10.13
        }
      ],
      defaultMarkerOptions: {
        color: 'orange',
        icon: 'smile',
        prefix: 'img'
      }
    }

    document.body.innerHTML = `
        <div id="searchpanel"></div>
        <div class="vmap" id="map"
             style="width: 500px; height: 500px;"
             data-options="${escape(JSON.stringify(options))}"></div>`

    require('@php/Lib/View/vMap')
  })

  afterEach(() => {
    resetModules()
  })

  it('gets initialized by leaflet', () => {
    return later(() => {
      assert.strictEqual(document.querySelectorAll('.leaflet-map-pane').length, 1)
    })
  })
})

function escape (str) {
  return str.replace(/[&<>"']/g, m => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  }[m]))
}
