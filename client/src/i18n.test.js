import assert from 'assert'

import i18n from '@/i18n'
// FIXME find a way of mocking this import
// right now the test strings are in there and i18n is importing
// import en from '@translations/messages.en.yml'

describe('i18n', () => {
  it('basic translation', () => {
    assert.strictEqual(i18n('test.plain'), 'This is a nice translation')
  })

  it('translation with variables', () => {
    assert.strictEqual(i18n('test.vars', {
      NAME: 'Peter',
      AGE: 23
    }), 'My name is Peter and I am 23 years old')
  })

  it('responds with placeholder if missing key', () => {
    assert.strictEqual(i18n('test.doesnotexist'), 'test.doesnotexist')
  })

  it('complains with missing variables', () => {
    assert.throws(() => i18n('test.vars', { NAME: 'peter' }))
  })

  it('accepts falsy value as variable', () => {
    assert.strictEqual(i18n('test.vars', {
      NAME: '',
      AGE: 0
    }), 'My name is  and I am 0 years old')
  })
})
