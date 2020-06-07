import assert from 'assert'
import { callableNumber } from '@/utils'

describe('callableNumber', () => {
  describe('with number input', () => {
    it('should render numbers as link', () => {
      assert.strictEqual(callableNumber('123'), 'tel:123')
    })
    it('should include leading country codes with +', () => {
      assert.strictEqual(callableNumber('+15555550123'), 'tel:+15555550123')
    })
  })
  describe('with text input', () => {
    it('should ignore empty input', () => {
      assert.strictEqual(callableNumber(''), '')
    })
    it('should ignore text-only input', () => {
      assert.strictEqual(callableNumber('CARGO BIKE MOUNTED ON TOP OF ANOTHER BIKE!'), '')
    })
  })
  describe('with mixed input', () => {
    it('should ignore whitespace', () => {
      assert.strictEqual(callableNumber(' 456 '), 'tel:456')
    })
    it('should ignore separators and leading/trailing text', () => {
      assert.strictEqual(callableNumber('(+49 9991) 456-879 Car!'), 'tel:+499991456879')
    })
  })
})
