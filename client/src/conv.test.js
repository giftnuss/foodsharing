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
      const { ajax } = require('@/script')
      sandbox.stub(conv, 'init')
      sandbox.stub(ajax, 'req').callsFake((app, method, { data, success }) => {
        // It makes an ajax request with the fsid
        assert.equal(app, 'msg')
        assert.equal(method, 'user2conv')
        assert.equal(data.fsid, fsId)
        // The server would send back a cid value
        success({ cid })
      })
      sandbox.stub(conv, 'chat').callsFake(val => {
        // It then triggers the chat
        assert.equal(val, cid)
      })
      conv.userChat(fsId)
      assert(conv.init.called)
      assert(ajax.req.called)
      assert(conv.chat.called)
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
