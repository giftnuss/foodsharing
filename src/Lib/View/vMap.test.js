/* eslint-env mocha */

import sinon from 'sinon'
import assert from 'assert'
import { resetModules } from '>/utils'
import $ from 'jquery'

class PlacesService { }

describe('vMap', () => {
  const sandbox = sinon.createSandbox()

  beforeEach(() => {
    sinon.stub($, 'getScript').callsFake((url, callback) => {
      global.google = { maps: { places: { PlacesService } } }
      callback()
    })

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
        <div id="map"
             style="width: 500px; height: 500px;"
             data-options="${escape(JSON.stringify(options))}"></div>`

    require('@php/Lib/View/vMap')
  })

  afterEach(() => {
    sandbox.restore()
    resetModules()
  })

  it('gets initialized by leaflet', () => {
    assert.equal(document.querySelectorAll('.leaflet-map-pane').length, 1)
    assert($.getScript.called)
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
