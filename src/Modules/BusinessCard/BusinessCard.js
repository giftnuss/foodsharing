/* eslint-disable camelcase */
import '@/core'
import '@/globals'
import $ from 'jquery'
import { expose } from '@/utils'

expose({
  u_download
})

function u_download (short) {
  $('#dlbox').show()
  $('#dlbox a').attr('href', `/?page=bcard&a=dl&b=${short}`)
}
