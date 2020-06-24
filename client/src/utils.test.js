import assert from 'assert'
import { callableNumber } from '@/utils'

describe('callableNumber', () => {
  describe('with number input', () => {
    it('should include leading country codes with +', () => {
      assert.strictEqual(callableNumber('+1 206 555 0100'), 'tel:+12065550100')
    })
    it('should handle ^00 the same way as ^+', () => {
      assert.strictEqual(callableNumber('001 206 555 0100'), 'tel:+12065550100')
    })
    it('should guess Germany as country code', () => {
      assert.strictEqual(callableNumber('01760100045'), 'tel:+491760100045')
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
      assert.strictEqual(callableNumber(' +12065550100 '), 'tel:+12065550100')
    })
    it('should ignore separators and leading/trailing text', () => {
      assert.strictEqual(callableNumber('(+49 1760) 100-045 Car!'), 'tel:+491760100045')
    })
    // don't accidentally call service lines by parsing just a few numbers from text
    it('should ignore numbers that are too short to be valid', () => {
      assert.strictEqual(callableNumber('1 bike, 1 dolly, 2 cars'), '')
    })
  })
})
