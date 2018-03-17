import $ from 'jquery'
import { ajreq } from '@/script'
import 'jquery-ui'
import 'jquery.ui.position'

import 'fullpage.js'
import 'jquery-contextmenu'

import '@/g'
import '@/menu'
import '@/becomeBezirk'
import '@/bezirkChildChooser'

import '@php/Lib/View/vPageslider'

/*
import 'jquery-ui'
import 'jquery-ui-addons'
import 'jquery-tablesorter'
import 'jquery-fancybox'
import 'jquery-jcrop'
import 'jquery-tagedit-auto-grow-input'
import 'jquery-tagedit'
import 'timeago'
import 'autolink'
import 'js-time-format'
import 'jquery-slimscroll'
import 'underscore'
import 'underscore-string'
import 'script'
import 'instant-search'
import 'conv'
import 'info'
import 'storage'
import 'jquery-popup'
import 'socket-io'
import 'socket'
import 'typeahead'
import 'typeahead-address-picker'
import 'leaflet'
*/

import { ServerData } from './utils'
import socket from '@/socket'
import info from '@/info'

console.log('running main!')

// MOVED FROM lib/inc.php

$('#mainMenu > li > a').each(function () {
  if (parseInt(this.href.length) > 2 && this.href.indexOf(ServerData.page) > 0) {
    $(this).parent().addClass('active').click(function (ev) {
      // ev.preventDefault();
    })
  }
})

$('#fs-profile-rate-comment').dialog({
  modal: true,
  title: '',
  autoOpen: false,
  buttons:
    [
      {
        text: 'Abbrechen',
        click: function () {
          $('#fs-profile-rate-comment').dialog('close')
        }
      },
      {
        text: 'Absenden',
        click: function () {
          ajreq('rate', {app: 'profile', type: 2, id: $('#profile-rate-id').val(), message: $('#fsprofileratemsg').val()})
        }
      }
    ]
}).siblings('.ui-dialog-titlebar').remove()


if (ServerData.user.may) {
  socket.connect()
  info.init()
} else {
  clearInterval(g_interval_newBasket)
}
