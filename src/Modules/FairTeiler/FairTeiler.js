/* eslint-disable camelcase */
import '@/core'
import '@/globals'
import $ from 'jquery'
import 'jquery-tagedit'
import 'jquery-tagedit-auto-grow-input'
import 'jquery-jcrop'
import 'typeahead'
import 'typeahead-addresspicker'
import 'leaflet'
import 'leaflet.awesome-markers'
import {
  ajreq,
  pictureCrop,
  pictureReady
} from '@/script'
import { expose } from '@/utils'
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
