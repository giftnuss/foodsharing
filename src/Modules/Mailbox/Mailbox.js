/* eslint-disable eqeqeq,camelcase */
import '@/core'
import '@/globals'
import '@/tablesorter'
import $ from 'jquery'
import 'jquery-dynatree'
import 'typeahead'
import { expose } from '@/utils'
import {
  ajreq,
  hideLoader,
  showLoader,
  u_loadCoords,
  u_handleNewEmail,
  pulseInfo,
  checkEmail
} from '@/script'
import './Mailbox.css'

expose({
  u_getGeo,
  mb_finishFile,
  mb_removeLast,
  mb_new_message,
  mb_mailto,
  mb_moveto,
  mb_reset,
  mb_answer,
  mb_forward,
  mb_setMailbox,
  u_loadBody,
  u_readyBody,
  mb_clearEditor,
  mb_closeEditor,
  mb_send_message,
  u_goAll,
  mb_refresh,
  checkEmail
})

function u_getGeo (id) {
  showLoader()

  if ($('#fs' + id + 'plz').val() != '' && $('#fs' + id + 'stadt').val() != '' && $('#fs' + id + 'anschrift').val() != '') {
    u_loadCoords({
      plz: $('#fs' + id + 'plz').val(),
      stadt: $('#fs' + id + 'stadt').val(),
      anschrift: $('#fs' + id + 'anschrift').val(),
      complete: function () {
        hideLoader()
      }
    }, function (lat, lon) {
      ajreq('updateGeo', {lat: lat, lon: lon, id: id})
    })
  }
}

function mb_finishFile (newname) {
  $('ul#et-file-list li:last').addClass('finish').append('<input type="hidden" class="tmp" value="' + newname + '" name="tmp_' + $('ul#et-file-list li').length + '" />')
  $('#etattach-button').button('option', 'disabled', false)
}

function mb_removeLast () {
  $('ul#et-file-list li:last').remove()
  $('#etattach-button').button('option', 'disabled', false)
}

function mb_new_message (email) {
  mb_clearEditor()
  $('#message-editor').dialog('open')
  if ($('.edit-an').length > 0) {
    $('.edit-an')[0].focus()
  }
  if (email != undefined) {
    $('.edit-an:first').val(email)
    u_handleNewEmail(email, $('.edit-an:first'))
    $('#edit-subject')[0].focus()
  }
}

function mb_mailto (email) {
  mb_clearEditor()
  $('.edit-an:first').val(email)
  $('#message-body').dialog('close')
  $('#message-editor').dialog('open')
  if ($('#edit-subject').length > 0) {
    $('#edit-subject')[0].focus()
  }
}

function mb_moveto (folder) {
  folder = parseInt(folder)
  if (folder > 0) {
    ajreq('move', {
      mid: $('#mb-hidden-id').val(),
      f: folder
    })
  }
}

function mb_reset () {
  $('#et-file-list').html('')
}

function mb_answer () {
  $('#edit-body').val($('#mailbox-body-plain').val())
  $('#edit-reply').val($('#mb-hidden-id').val())
  mb_reset()

  let subject = $('#mb-hidden-subject').val()
  if (subject.substring(0, 3) != 'Re:') {
    subject = 'Re: ' + subject
  }

  $('#message-editor').dialog('option', {
    title: subject
  })

  $('#edit-subject').val(subject)
  $('input.edit-an:first').val($('#mb-hidden-email').val())

  u_handleNewEmail($('input.edit-an:first').val(), $('input.edit-an:first'))

  $('#message-body').dialog('close')
  $('#message-editor').dialog('open')

  if ($('#edit-body').length > 0) {
    $('#edit-body')[0].focus()
  }
}

function mb_forward () {

}

function mb_setMailbox (mb_id) {
  if ($('#edit-von').length > 0) {
    let email = $('#edit-von option.mb-' + mb_id).text()
    $('#edit-von option.mb-' + mb_id).remove()
    let html = $('#edit-von').html()
    $('#edit-von').html('')

    $('#edit-von').html('<option value="' + mb_id + '" class="mb-' + mb_id + '">' + email + '</option>' + html)
  }
}

function u_loadBody () {
  if ($('.mailbox-body iframe').length > 0) {
    $('.mailbox-body-loader').show()
    $('.mailbox-body').hide()
  }
}

function u_readyBody () {
  hideLoader()
  $('.mailbox-body').show()
  $('.mailbox-body-loader').hide()
}

function mb_clearEditor () {
  $('#edit-von').val('')
  for (let i = 1; i < $('.edit-an').length; i++) {
    $('.edit-an:last').parent().parent().parent().remove()
  }
  $('.edit-an').val('')
  $('#edit-subject').val('')
  $('#edit-body').val('')
  $('#edit-reply').val('0')
  $('#message-editor').dialog('option', {
    title: 'Neue Nachricht'
  })
  mb_reset()
}

function mb_closeEditor () {
  $('#message-editor').dialog('close')
}

function mb_send_message () {
  let mbid = $('#h-edit-von').val()
  if ($('#edit-von').length > 0) {
    mbid = $('#edit-von').val()
  }

  let attach = []
  let i = 0
  $('#et-file-list li').each(function () {
    attach[i] = {
      name: $(this).text(),
      tmp: $('#et-file-list li input')[i].value
    }

    i++
  })

  let an = ''
  $('.edit-an').each(function () {
    an = an + ';' + $(this).val()
  })

  if (an.indexOf('@') == -1) {
    $('.edit-an')[0].focus()
    pulseInfo('Du musst einen Empfänger angeben')
  } else {
    ajreq('send_message', {
      mb: mbid,
      an: an.substring(1),
      sub: $('#edit-subject').val(),
      body: $('#edit-body').val(),
      attach: attach,
      reply: parseInt($('#edit-reply').val())
    }, 'post')
  }
}

function u_goAll () {

}

function mb_refresh () {
  ajreq('loadmails', {
    mb: $('#mbh-mailbox').val(),
    folder: $('#mbh-folder').val(),
    type: $('#mbh-type').val()
  })
}
