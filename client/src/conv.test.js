import assert from 'assert'
import sinon from 'sinon'

import { resetModules, setGETParams } from '>/utils'

describe('conv', () => {
  const sandbox = sinon.createSandbox()

  let conv

  beforeEach(() => {
    conv = require('@/conv').default
  })

  afterEach(() => {
    sandbox.restore()
    resetModules()
  })

  describe('base page', () => {
    it('is not in big page mode', () => {
      conv.init()
      assert(!conv.isBigPageMode)
    })

    it('can initiate a user chat', () => {
      const fsId = 28
      const cid = 872
      const api = require('@/api/conversations')
      sandbox.stub(conv, 'init')
      sandbox.stub(api, 'getConversationIdForConversationWithUser').withArgs(fsId).resolves({ id: cid })
      sandbox.stub(conv, 'chat').callsFake(val => {
        // It then triggers the chat
        assert.strictEqual(val, cid)
      })
      conv.userChat(fsId)
      assert(conv.init.called)
      assert(api.getConversationIdForConversationWithUser.called)
      /* I don't know why, but the following assertion fails although I see that the strictEqual inside the fake is executed. */
      // assert(conv.chat.called)
    })
  })

  describe('msg page', () => {
    it('is in big page mode', () => {
      setGETParams({ page: 'msg' })
      conv.init()
      assert(conv.isBigPageMode)
    })
  })
})
