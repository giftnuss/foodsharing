/* eslint-disable eqeqeq,camelcase */
import $ from 'jquery'
import '@/core'
import '@/globals'
import '@/tablesorter'
import i18n from '@/i18n'
import { verifyUser, deverifyUser } from '@/api/verification'
import {
  showLoader,
  hideLoader,
  pulseError,
  goTo,
  checkAllCb,
} from '@/script'
import {
  expose,
} from '@/utils'
import './PassportGenerator.css'

expose({
  checkAllCb,
})

let verify_fid = 0
let verify_el = null
$('#verifyconfirm-dialog').dialog({
  autoOpen: false,
  modal: true,
  buttons: {
    [i18n('pass.button.verify')]: async function () {
      $(this).parent().find('button.ui-button').prop('disabled', true)
      showLoader()
      try {
        await verifyUser(verify_fid)
        verify_el.removeClass('verify-do')
        verify_el.addClass('verify-undo')
      } catch (err) {
        console.error(err)
        pulseError(i18n('error_unexpected'))
      } finally {
        $(this).parent().find('button.ui-button').prop('disabled', false)
        hideLoader()
        $(this).dialog('close')
      }
    },
    [i18n('button.cancel')]: function () {
      $(this).dialog('close')
    },
  },
})

$('#unverifyconfirm-dialog').dialog({
  autoOpen: false,
  modal: true,
  buttons: {
    [i18n('pass.button.check')]: function () {
      goTo(`/profile/${verify_fid}`)
    },
    [i18n('button.cancel')]: function () {
      $(this).dialog('close')
    },
  },
})

$('.checker').on('click', function (el) {
  const $this = $(this)
  if ($this[0].checked) {
    $(`input.checkbox.bezirk${$this.attr('value')}`).prop('checked', true)
  } else {
    $(`input.checkbox.bezirk${$this.attr('value')}`).prop('checked', false)
  }
})

$('.verify').on('click', async function () {
  const $this = $(this)

  verify_el = $this
  verify_fid = $this.parent().parent().children('td:first').children('input').val()

  if ($this.hasClass('verify-do')) {
    $('#verifyconfirm-dialog').dialog('open')
  } else {
    verify_el.css('pointer-events', 'none')
    showLoader()
    try {
      await deverifyUser(verify_fid)
      $this.removeClass('verify-undo')
      $this.addClass('verify-do')
    } catch (err) {
      if (err.code === 400) {
        $('#unverifyconfirm-dialog').dialog('open')
      } else {
        console.error(err)
        pulseError(i18n('error_unexpected'))
      }
    } finally {
      verify_el.css('pointer-events', 'auto')
      hideLoader()
    }
  }

  return false
})

$('a.fsname').on('click', function () {
  const $this = $(this)
  if ($(`input[value='${$this.next().val()}']`)[0].checked) {
    $(`input[value='${$this.next().val()}']`).prop('checked', false)
  } else {
    $(`input[value='${$this.next().val()}']`).prop('checked', true)
  }
  return false
})

$("a[href='#start']").on('click', function () {
  $('form#generate').trigger('submit')
  return false
})

$('a.dateclick').on('click', function () {
  const $this = $(this)
  const dstr = $this.next().val()
  if ($(`input.date${dstr}`)[0].checked) {
    $(`input.date${dstr}`).prop('checked', false)
  } else {
    $(`input.date${dstr}`).prop('checked', true)
  }
  return false
})
