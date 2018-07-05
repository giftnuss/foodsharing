/* eslint-env mocha */

import assert from 'assert'
import timeformat from '@/timeformat'

describe('timeformat', () => {
  describe('niceDate', () => {
    it('should format a date string', () => {
      assert.equal(timeformat.niceDate('2018-20-32'), '32.20.2018')
    })
  })
  describe('niceDateTime', () => {
    it('format a date and time string', () => {
      assert.equal(timeformat.niceDateTime('2018-3-31 06:23'), '31.3.2018 06.23 Uhr')
    })
  })
})
