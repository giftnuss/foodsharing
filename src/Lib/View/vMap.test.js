/* eslint-env mocha */

import sinon from 'sinon'
import assert from 'assert'
import { resetModules } from '>/utils'

const sandbox = sinon.createSandbox()

describe('vMap', () => {
  let server

  beforeEach(() => {
    server = sinon.createFakeServer()
    document.body.innerHTML = `<div id="map" style="width: 500px; height: 500px;" data-options="${escape(JSON.stringify({
      center: [50.89, 10.13],
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
    }))}"></div>`
    require('@php/Lib/View/vMap')
  })

  afterEach(() => {
    server.restore()
    sandbox.restore()
    resetModules()
  })

  it('gets initialized by leaflet', () => {
    assert.equal(document.querySelectorAll('.leaflet-map-pane').length, 1)
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
