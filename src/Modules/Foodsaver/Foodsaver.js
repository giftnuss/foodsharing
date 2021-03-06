import '@/core'
import '@/globals'
import { ajreq, GET, goTo, pulseSuccess } from '@/script'
import { expose } from '@/utils'
import $ from 'jquery'
import 'jquery-dynatree'
import i18n from '@/i18n'
import { deleteUser } from '@/api/user'
import './Foodsaver.css'

const fsapp = {
  init: function () {
    if ($('#fslist').length > 0) {
      $('#fslist a').on('click', function (ev) {
        ev.preventDefault()
        const fsida = $(this).attr('href').split('#')
        const fsid = parseInt(fsida[(fsida.length - 1)])
        fsapp.loadFoodsaver(fsid)
      })
    }
  },
  loadFoodsaver: function (foodsaverId) {
    ajreq('loadFoodsaver', {
      app: 'foodsaver',
      id: foodsaverId,
      bid: GET('bid'),
    })
  },
  refreshFoodsaver: function () {
    ajreq('foodsaverrefresh', {
      app: 'foodsaver',
      bid: GET('bid'),
    })
  },
  deleteFromRegion: function (foodsaverId) {
    if (window.confirm(i18n('deletefromregion'))) {
      ajreq('deleteFromRegion', {
        app: 'foodsaver',
        bid: GET('bid'),
        id: foodsaverId,
      })
    }
  },
}

export async function confirmDeleteUser (fsId, name) {
  if (window.confirm(i18n('foodsaver.delete_account_sure', { name }))) {
    await deleteUser(fsId)
    pulseSuccess(i18n('success'))
    goTo('/?page=dashboard')
  }
}

fsapp.init()

expose({
  fsapp,
  confirmDeleteUser,
})
