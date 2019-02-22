import 'whatwg-fetch'

import '@/raven'

import '@/style'

import $ from 'jquery'
import 'jquery-migrate'

import { initialize, ajreq } from '@/script'

import 'jquery-ui'
import 'fullpage.js'
import registerServiceWorker from '@/registerServiceWorker'
import 'jquery-contextmenu'
import 'jquery-contextmenu/dist/jquery.ui.position'
import 'jquery-contextmenu/dist/jquery.contextMenu.css'
import '@/jquery.contextMenu.overrides.css'
import './scss/bootstrap-theme.scss'
import './scss/index.scss'


// TODO: join dynamic form could be on any page - fix this
import '@/join'

import '@/menu'
import '@/becomeBezirk'

import serverData from '@/server-data'

import socket from '@/socket'
import { getCsrfToken } from '@/api/base'

initialize()
registerServiceWorker()


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
} else {
  clearInterval(window.g_interval_newBasket)
}

// add CSRF-Token to all jquery requests
$.ajaxPrefilter(function (options) {
  if (!options.beforeSend) {
    options.beforeSend = function (xhr, settings) {
      if (settings.url.startsWith('/') && !settings.url.startsWith('//')) {
        xhr.setRequestHeader('X-CSRF-Token', getCsrfToken())
      } else {
        // don't send for external domains (must be a relative url)
      }
    }
  }
})
