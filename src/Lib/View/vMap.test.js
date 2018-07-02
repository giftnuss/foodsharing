/* eslint-env mocha */

import sinon from 'sinon'
import assert from 'assert'
import { resetModules } from '>/utils'

const sandbox = sinon.createSandbox()

describe('vMap', () => {
  let vMap
  let server

  beforeEach(() => {
    server = sinon.createFakeServer()
    vMap = require('@php/Lib/View/vMap')
  })

  afterEach(() => {
    server.restore()
    sandbox.restore()
    resetModules()
  })

  it('at least has loaded and exists', () => {
    assert(vMap)
  })
})
