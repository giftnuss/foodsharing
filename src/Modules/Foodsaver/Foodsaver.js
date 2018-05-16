import '@/core'
import '@/globals'
import { ajreq, GET } from '@/script'
import $ from 'jquery'

const fsapp = {
  init: function () {
    if ($('#fslist').length > 0) {
      $('#fslist a').click(function (ev) {
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
  }
}

fsapp.init()

window.fsapp = fsapp
