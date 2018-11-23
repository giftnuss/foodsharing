/* eslint-disable eqeqeq,camelcase */
import '@/core'
import '@/globals'
import '@/tablesorter'
import $ from 'jquery'
import 'jquery-dynatree'
import 'typeahead'
import 'jquery-tagedit'
import 'jquery-tagedit-auto-grow-input'
import { expose } from '@/utils'
import {
  ajreq,
  hideLoader,
  showLoader,
  u_loadCoords,
  pulseInfo,
  pulseError,
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
  checkEmail,
  u_handleNewEmail,
  u_addTypeHead,
  setAutocompleteAddresses
})

function u_getGeo (id) {
  showLoader()

  if ($(`#fs${id}plz`).val() != '' && $('#fs' + id + 'stadt').val() != '' && $('#fs' + id + 'anschrift').val() != '') {
    u_loadCoords({
      plz: $(`#fs${id}plz`).val(),
      stadt: $(`#fs${id}stadt`).val(),
      anschrift: $(`#fs${id}anschrift`).val(),
      complete: function () {
        hideLoader()
      }
    }, function (lat, lon) {
      ajreq('updateGeo', { lat: lat, lon: lon, id: id })
    })
  }
}

function mb_finishFile (newname) {
  $('ul#et-file-list li:last').addClass('finish').append(`<input type="hidden" class="tmp" value="${newname}" name="tmp_${$('ul#et-file-list li').length}" />`)
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
    subject = `Re: ${subject}`
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
    let email = $(`#edit-von option.mb-${mb_id}`).text()
    $(`#edit-von option.mb-${mb_id}`).remove()
    let html = $('#edit-von').html()
    $('#edit-von').html('')

    $('#edit-von').html(`<option value="${mb_id}" class="mb-${mb_id}">${email}</option>${html}`)
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
    an = `${an};${$(this).val()}`
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

var substringMatcher = function (strs) {
  return function findMatches (q, cb) {
    // regex used to determine if a string contains the substring `q`
    var substringRegex = new RegExp(q, 'i')

    // an array that will be populated with substring matches
    var matches = []

    // iterate through the pool of strings and for any string that
    // contains the substring `q`, add it to the `matches` array
    $.each(strs, function (i, str) {
      if (substringRegex.test(str)) {
        matches.push({ value: str })
      }
    })

    cb(matches)
  }
}

let addresses = []

function setAutocompleteAddresses (adr) {
  addresses = adr
}

function u_addTypeHead () {
  $('.edit-an').typeahead('destroy')
  $('.edit-an:last').typeahead({
    hint: true,
    minLength: 2
  }, {
    name: 'addresses',
    source: substringMatcher(addresses),
    limit: 15
  })

  $('.edit-an').on('typeahead:selected typeahead:autocompleted', function (e, datum) {
    window.setTimeout(() => (u_handleNewEmail(this.value, $(this))), 100)
  }).on('blur', function () {
    let $this = this
    if ($this.value != '' && !checkEmail($this.value)) {
      pulseError('Diese E-Mail-Adresse ist nicht korrekt')
      $this.focus()
    } else if ($this.value != '') {
      window.setTimeout(() => (u_handleNewEmail(this.value, $(this))), 100)
    }
  })
}

function u_handleNewEmail (email, el) {
  if (u_anHasChanged()) {
    let availmail = []
    let availmail_count = 0
    $('.edit-an').each(function () {
      let $this = $(this)
      if (!checkEmail($this.val()) || (availmail[$this.val()] != undefined)) {
        // $this.parent().parent().parent().remove();
      } else {
        availmail[$this.val()] = true
        availmail_count++
      }
    })

    $('#mail-subject').before('<tr><td class="label">&nbsp;</td><td class="data"><input type="text" name="an[]" class="edit-an" value="" /></td></tr>')

    u_addTypeHead()
    var height = $('#edit-body').height() - (availmail_count * 28)
    if (height > 40) {
      $('#edit-body').css('height', `${height}px`)
    }

    $('.edit-an:last').focus()
  }
}

let mailcheck = ''

function u_anHasChanged () {
  let check = ''
  $('.edit-an').each(function () {
    check += this.value
  })
  if (mailcheck == '') {
    mailcheck = check
    return true
  } else if (mailcheck != check) {
    mailcheck = check
    return true
  } else {
    return false
  }
}
