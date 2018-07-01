/* eslint-env mocha */

import sinon from 'sinon'
import assert from 'assert'
import * as browser from '@/browser'
import msg from '@/msg'
import { sleep, jsonOK } from '>/utils'

const sandbox = sinon.createSandbox()

let script
let mockBrowser
let server
let GETParams

function setGETParams (params) {
  Object.assign(GETParams, params)
}

beforeEach(() => {
  mockBrowser = sandbox.mock(browser)
  script = require('@/script')
  server = sinon.createFakeServer()
  GETParams = {}
  sandbox.stub(browser, 'GET').callsFake(param => GETParams[param])
  sandbox.stub(msg, 'loadConversation')
})

afterEach(() => {
  mockBrowser.verify()
  server.restore()
  sandbox.restore()
  delete require.cache[require.resolve('@/script')]
})

describe('script', () => {
  describe('on mobile', () => {
    beforeEach(() => sandbox.stub(browser, 'isMob').returns(true))

    describe('isMob', () => {
      it('works', () => {
        assert.equal(script.isMob(), true)
      })
    })

    describe('chat', () => {
      const fsId = 82
      const conversationId = 10

      beforeEach(() => {
        server.respondWith(
          `/xhrapp.php?app=msg&m=user2conv&fsid=${fsId}`,
          jsonOK({ status: 1, data: { cid: conversationId } })
        )
      })

      it('redirects to user chat page', () => {
        mockBrowser
          .expects('goTo')
          .once()
          .withArgs(`/?page=msg&cid=${conversationId}`)

        script.chat(fsId)
        server.respond()
      })

      it('on msg page it loads the conversation', () => {
        mockBrowser
          .expects('goTo')
          .never()

        setGETParams({ page: 'msg' })

        script.chat(fsId)
        server.respond()

        assert(msg.loadConversation.called)
      })
    })
  })

  describe('on desktop', () => {
    beforeEach(() => sandbox.stub(browser, 'isMob').returns(false))

    it('is not mobile!', () => {
      assert.equal(script.isMob(), false)
    })

    it('can initialize', () => {
      script.initialize()
    })

    describe('pulse', () => {
      let info
      let success
      let error
      beforeEach(() => {
        info = document.getElementById('pulse-info')
        success = document.getElementById('pulse-success')
        error = document.getElementById('pulse-error')
      })
      afterEach(() => {
        for (const el of [info, success, error]) {
          el.style.display = 'none'
        }
      })
      it('can show info', () => {
        const message = 'a nice info message'
        script.pulseInfo(message, { timeout: 0 })
        assert.equal(info.innerHTML, message)
      })

      it('can show success', () => {
        const message = 'a nice success message'
        script.pulseSuccess(message, { timeout: 0 })
        assert.equal(success.innerHTML, message)
      })

      it('can show error', () => {
        const message = 'a nice error message'
        script.pulseError(message, { timeout: 0 })
        assert.equal(error.innerHTML, message)
      })

      it('will be hidden after a timeout', async () => {
        const message = 'a nice message'
        assert.equal(info.style.display, 'none')
        script.pulseInfo(message, { timeout: 0 })
        assert(['block', ''].includes(info.style.display))
        await sleep(20)
        assert.equal(info.style.display, 'none')
      })
    })
  })
})
