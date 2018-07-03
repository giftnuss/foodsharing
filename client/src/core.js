import '@/raven'

import '@/style'

import $ from 'jquery'
import { initialize, ajreq } from '@/script'

import 'jquery-ui'

import 'fullpage.js'

import 'jquery-contextmenu'
import 'jquery-contextmenu/dist/jquery.ui.position'
import 'jquery-contextmenu/dist/jquery.contextMenu.css'

import '@/menu'
import '@/becomeBezirk'

import serverData from '@/server-data'

import socket from '@/socket'
import info from '@/info'
import search from '@/instant-search'

initialize()

$('#mainMenu > li > a').each(function () {
  if (parseInt(this.href.length) > 2 && this.href.indexOf(serverData.page) > 0) {
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

if (serverData.user.may) {
  socket.connect()
  info.init()
  search.init()
} else {
  clearInterval(window.g_interval_newBasket)
}
