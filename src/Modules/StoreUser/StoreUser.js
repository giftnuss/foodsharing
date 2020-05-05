/* eslint-disable eqeqeq,camelcase */

import '@/core'
import '@/globals'

import i18n from '@/i18n'

import { expose } from '@/utils'

import $ from 'jquery'
import {
  ajax,
  pulseError, pulseInfo,
  showLoader,
  hideLoader,
  GET
} from '@/script'

import 'jquery-tagedit'
import 'jquery-tagedit-auto-grow-input'
import '@/tablesorter'

import {
  u_updatePosts,
  u_betrieb_sign_out,
  u_delPost,
  acceptRequest,
  warteRequest,
  denyRequest
} from './StoreUser.lib'
import { vueApply, vueRegister } from '@/vue'
import PickupHistory from './components/PickupHistory'
import PickupList from './components/PickupList'
import StoreInfos from './components/StoreInfos'
import StoreTeam from './components/StoreTeam'
import { deleteStorePost } from '@/api/stores'

expose({
  u_updatePosts,
  u_betrieb_sign_out,
  u_delPost,
  acceptRequest,
  warteRequest,
  denyRequest
})

$(document).ready(() => {
  $('.cb-verantwortlicher').on('click', function () {
    if ($('.cb-verantwortlicher:checked').length >= 4) {
      pulseError(i18n('max_3_leader'))
      return false
    }
  })

  $('#team-form').on('submit', function (ev) {
    if ($('.cb-verantwortlicher:checked').length == 0 && $('#set_new_store_manager').val() != 'true') {
      pulseError(i18n('verantwortlicher_must_be'))
      ev.preventDefault()
      return false
    } else if ($('#set_new_store_manager').val() == 'true' && $('.tagedit-listelement-old').length > 3) {
      pulseError(i18n('max_3_leader'))
      return false
    }
  })

  $('#comment-post').hide()

  $('div#pinnwand form textarea').on('focus', function () {
    $('#comment-post').show()
  })

  $('div#pinnwand form input.submit').button().on('keydown', function (event) {
    $('div#pinnwand form').trigger('submit')
  })

  $('div#pinnwand form').on('submit', function (e) {
    e.preventDefault()
    const postTextArea = $('div#pinnwand form textarea')
    if (postTextArea.val() !== postTextArea.attr('title')) {
      const submitButton = $('#comment-post')
      submitButton.prop('disabled', true)
      const storeId = GET('id')
      $.ajax({
        dataType: 'json',
        data: $('div#pinnwand form').serialize(),
        method: 'POST',
        url: `/api/stores/${storeId}/posts`,
        success: function () {
          // update posts list
          u_updatePosts()
          // Reset input field
          postTextArea.val(postTextArea.attr('title'))
          // enable disabled submit button again
          submitButton.prop('disabled', false)
        },
        error: function (error) {
          // enable disabled submit button again also in case of error
          submitButton.prop('disabled', false)
          // handle error
          pulseError(error.responseJSON.message)
        }
      })
    }
  })

  $('#signout_shure').dialog({
    autoOpen: false,
    modal: true,
    buttons: [
      {
        text: $('#signout_shure .sure').text(),
        click: function () {
          showLoader()

          ajax.req('betrieb', 'signout', {
            data: { id: GET('id') },
            success: function () {

            }
          })
        }
      },
      {
        text: $('#signout_shure .abort').text(),
        click: function () {
          $('#signout_shure').dialog('close')
        }
      }
    ]
  })

  $('#delete_shure').dialog({
    autoOpen: false,
    modal: true,
    buttons: [
      {
        text: $('#delete_shure .sure').text(),
        click: async function () {
          showLoader()
          const pid = $(this).data('pid')
          try {
            await deleteStorePost(pid)
            $(`.bpost-${pid}`).remove()
            $('#delete_shure').dialog('close')
          } catch (e) {
            pulseError(i18n('error_unexpected'))
          }
          hideLoader()
        }
      },
      {
        text: $('#delete_shure .abort').text(),
        click: function () {
          $('#delete_shure').dialog('close')
        }
      }
    ]
  })

  $('.nft-remove').button({
    text: false,
    icons: {
      primary: 'ui-icon-minus'
    }
  }).on('click', function () {
    const $this = $(this)
    $this.parent().parent().remove()
  })

  $('.timetable').on('keyup', '.fetchercount', function () {
    if (this.value != '') {
      let val = parseInt(`0${this.value}`, 10)
      if (val == 0) {
        val = 1
      } else if (val > 2) {
        pulseInfo(i18n('max_2_foodsaver'), {
          sticky: true
        })
      }
      this.value = val
    }
  })

  $('#nft-add').button({
    text: false
  }).on('click', function () {
    $('table.timetable tbody').append($('table#nft-hidden-row tbody').html())
    let clname = 'odd'
    $('table.timetable tbody tr').each(function () {
      if (clname == 'odd') {
        clname = 'even'
      } else {
        clname = 'odd'
      }

      const $this = $(this)
      $this.removeClass('odd even')
      $this.addClass(clname)
    })
    $('.nft-remove').button({
      text: false,
      icons: {
        primary: 'ui-icon-minus'
      }
    }).on('click', function () {
      const $this = $(this)
      $this.parent().parent().remove()
    })
  })

  vueRegister({
    PickupHistory,
    PickupList,
    StoreInfos,
    StoreTeam
  })
  vueApply('#vue-pickup-history', true)
  vueApply('#vue-pickuplist', true)
  vueApply('#vue-storeinfos', true)
  vueApply('#vue-storeteam', true)
})
