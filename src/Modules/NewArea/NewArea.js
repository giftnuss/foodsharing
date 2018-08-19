/* eslint-disable eqeqeq,camelcase */
import '@/core'
import '@/globals'
import $ from 'jquery'
import '@/tablesorter'
import 'jquery-dynatree'
import { expose } from '@/utils'
import { ajreq } from '@/script'
import './NewArea.css'

expose({
  deleteMarked,
  u_getChecked
})

function deleteMarked () {
  ajreq('deleteMarked', {
    del: u_getChecked()
  })
  $('.wantnewcheck:checked').parent().parent().remove()
}

function u_getChecked () {
  let del = ''
  $('.wantnewcheck:checked').each(function () {
    del += '-' + $(this).val()
  })
  return del.substring(1)
}

$('#orderFs').click(function (ev) {
  ev.preventDefault()
  let bid = parseInt($('#order_bezirk').val())
  ajreq('orderFs', {
    fs: u_getChecked(),
    bid: bid,
    msg: $('#order_msg').val(),
    subject: $('#subject').val()
  })
})
