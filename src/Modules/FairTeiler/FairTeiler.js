/* eslint-disable camelcase */
import '@/core'
import '@/globals'
import $ from 'jquery'
import 'jquery-tagedit'
import 'jquery-tagedit-auto-grow-input'
import 'jquery-jcrop'
import { attachAddresspicker } from '@/addresspicker'

import {
  ajreq,
  pictureCrop,
  pictureReady
} from '@/script'
import { expose } from '@/utils'
import { GET } from '@/browser'

import './FairTeiler.css'

expose({
  pictureCrop,
  pictureReady,
  u_wallpostReady
})

function u_wallpostReady (postid) {
  ajreq('infofollower', { fid: $('#ft-id').val(), pid: postid })
}

$('#wall-submit').bind('mousedown', function () {
  $('#ft-public-link').trigger('click')
})

let sub = GET('sub')
if (sub === 'add' || sub === 'edit') {
  attachAddresspicker()
}
