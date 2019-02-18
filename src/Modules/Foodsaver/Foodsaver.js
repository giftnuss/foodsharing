import '@/core'
import '@/globals'
import { ajreq, GET } from '@/script'
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
        let fsida = $(this).attr('href').split('#')
        let fsid = parseInt(fsida[(fsida.length - 1)])
        fsapp.loadFoodsaver(fsid)
      })
    }
  },
  loadFoodsaver: function (foodsaverId) {
    ajreq('loadFoodsaver', {
      app: 'foodsaver',
      id: foodsaverId,
      bid: GET('bid')
    })
  },
  refreshfoodsaver: function () {
    ajreq('foodsaverrefresh', {
      app: 'foodsaver',
      bid: GET('bid')
    })
  },
  delfromBezirk: function (foodsaverId) {
    if (window.confirm('Wirklich aus Bezirk löschen?')) {
      ajreq('delfrombezirk', {
        app: 'foodsaver',
        bid: GET('bid'),
        id: foodsaverId
      })
    }
  },
  confirmDeleteUser: async function (fsId) {
    if (window.confirm(i18n('foodsaver.delete_account_sure'))) {
      await deleteUser(fsId)
    }
  }
}

fsapp.init()

window.fsapp = fsapp
