/* eslint-disable eqeqeq,camelcase */

import '@/core'
import '@/globals'
import $ from 'jquery'

import 'jquery-tagedit'
import 'jquery-tagedit-auto-grow-input'

import i18n from '@/i18n'
import { expose } from '@/utils'
import './StoreUser.css'

import {
  ajax,
  pulseError, pulseInfo,
  showLoader,
  hideLoader,
  GET,
} from '@/script'
import '@/tablesorter' // Remove after replacing u_storeList

import {
  u_betrieb_sign_out,
  u_delPost,
  acceptRequest,
  warteRequest,
  denyRequest,
} from './StoreUser.lib'
import { vueApply, vueRegister } from '@/vue'
import PickupHistory from './components/PickupHistory'
import PickupList from './components/PickupList'
import Store from './components/Store'
import StoreInfos from './components/StoreInfos'
import StoreTeam from './components/StoreTeam'
import { deleteStorePost } from '@/api/stores'

expose({
  u_betrieb_sign_out,
  u_delPost,
  acceptRequest,
  warteRequest,
  denyRequest,
})

$(document).ready(() => {
  $('.cb-verantwortlicher').on('click', function () {
    if ($('.cb-verantwortlicher:checked').length >= 4) {
      pulseError(i18n('storeedit.team.max-sm'))
      return false
    }
  })

  $('#team-form').on('submit', function (ev) {
    if ($('.cb-verantwortlicher:checked').length == 0 && $('#set_new_store_manager').val() != 'true') {
      pulseError(i18n('storeedit.team.need-sm'))
      ev.preventDefault()
      return false
    } else if ($('#set_new_store_manager').val() == 'true' && $('.tagedit-listelement-old').length > 3) {
      pulseError(i18n('storeedit.team.max-sm'))
      return false
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

            },
          })
        },
      },
      {
        text: $('#signout_shure .abort').text(),
        click: function () {
          $('#signout_shure').dialog('close')
        },
      },
    ],
  })

  $('#delete_shure').dialog({
    autoOpen: false,
    modal: true,
    buttons: [
      {
        text: $('#delete_shure .sure').text(),
        click: async function () {
          showLoader()
          const storeId = GET('id')
          const postId = $(this).data('pid')
          try {
            await deleteStorePost(storeId, postId)
            $(`.bpost-${postId}`).remove()
            $('#delete_shure').dialog('close')
          } catch (e) {
            pulseError(i18n('error_unexpected'))
          }
          hideLoader()
        },
      },
      {
        text: $('#delete_shure .abort').text(),
        click: function () {
          $('#delete_shure').dialog('close')
        },
      },
    ],
  })

  $('.nft-remove').button({
    text: false,
    icons: {
      primary: 'ui-icon-trash',
    },
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
        pulseInfo(i18n('pickup.edit.many-people'), {
          sticky: true,
        })
      }
      this.value = val
    }
  })

  $('#nft-add').button({
    text: false,
  }).on('click', function () {
    $('table.timetable tbody').append($('table#nft-hidden-row tbody').html())
    $('.nft-remove').button({
      text: false,
      icons: {
        primary: 'ui-icon-trash',
      },
    }).on('click', function () {
      const $this = $(this)
      $this.parent().parent().remove()
    })
  })

  vueRegister({
    PickupHistory,
    PickupList,
    Store,
    StoreInfos,
    StoreTeam,
  })
  vueApply('#vue-pickup-history', true)
  vueApply('#vue-pickuplist', true)
  vueApply('#vue-storeview', true) // Store
  vueApply('#vue-storeinfos', true)
  vueApply('#vue-storeteam', true)
})
