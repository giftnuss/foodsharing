import assert from 'assert'

import serverData from '@/server-data'
import i18n from '@/i18n'

describe('i18n', () => {
  beforeEach(() => {
    Object.assign(serverData.translations, {
      foo: 'my nice translation',
      bar: 'my name is {NAME} and I am {AGE} years old'
    })
  })

  afterEach(() => {
    serverData.translations = {}
  })

  it('basic translation', () => {
    assert.strictEqual(i18n('foo'), 'my nice translation')
  })

  it('translation with variables', () => {
    assert.strictEqual(i18n('bar', {
      NAME: 'Peter',
      AGE: 23
    }), 'my name is Peter and I am 23 years old')
  })

  it('complains if missing key', () => {
    assert.throws(() => i18n('doesnotexist'))
  })

  it('complains with missing variables', () => {
    assert.throws(() => i18n('bar', { NAME: 'peter' }))
  })

  it('accepts falsy value as variable', () => {
    assert.strictEqual(i18n('bar', {
      NAME: '',
      AGE: 0
    }), 'my name is  and I am 0 years old')
  })
})
