/* eslint-env mocha */

import sinon from 'sinon'

export function jsonOK (data) {
  return [200, { 'Content-Type': 'application/json' }, JSON.stringify(data)]
}

export function sleep (ms) {
  return new Promise(resolve => setTimeout(resolve, ms))
}

export function resetModules () {
  Object.keys(require.cache).forEach(entry => {
    if (entry.startsWith('./src')) {
      delete require.cache[entry]
    }
  })
}

let GETParams

export function setGETParams (params) {
  Object.assign(GETParams, params)
}

const sandbox = sinon.createSandbox()

beforeEach(() => {
  GETParams = {}
  const browser = require('@/browser')
  sandbox.stub(browser, 'GET').callsFake(param => GETParams[param])
})

afterEach(() => {
  sandbox.restore()
})
