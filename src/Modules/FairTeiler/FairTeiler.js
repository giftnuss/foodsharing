/* eslint-disable camelcase */
import '@/core'
import '@/globals'
import $ from 'jquery'
import 'jquery-tagedit'
import 'jquery-tagedit-auto-grow-input'
import 'typeahead'
import 'typeahead-addresspicker'
import 'leaflet'
import 'leaflet.awesome-markers'
import {
  ajreq
} from '@/script'
import { expose } from '@/utils'
import './FairTeiler.css'

expose({
  u_wallpostReady
})

function u_wallpostReady (postid) {
  ajreq('infofollower', { fid: $('#ft-id').val(), pid: postid })
}

$('#wall-submit').bind('mousedown', function () {
  $('#ft-public-link').trigger('click')
})
