import '@/style'

import '@/globals' // causes this to be exported globally via an expose-loader

import $ from 'jquery'
import { ajreq } from '@/script'
import 'jquery-ui'

import 'fullpage.js'
import 'jquery-contextmenu'
import 'jquery-contextmenu/dist/jquery.ui.position'
import 'jquery-contextmenu/dist/jquery.contextMenu.css'

import '@/menu'
import '@/becomeBezirk'
import '@/bezirkChildChooser'

import { ServerData } from './utils'

import socket from '@/socket'
import info from '@/info'

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
          ajreq('rate', {
            app: 'profile',
            type: 2,
            id: $('#profile-rate-id').val(),
            message: $('#fsprofileratemsg').val()
          })
        }
      }
    ]
}).siblings('.ui-dialog-titlebar').remove()

if (ServerData.user.may) {
  socket.connect()
  info.init()
} else {
  // TODO: work out what this is about
  // clearInterval(g_interval_newBasket)
}
