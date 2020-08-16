/* eslint-disable camelcase,eqeqeq */

import $ from 'jquery'

import { hideLoader, showLoader, reload, GET } from '@/script'

export function u_updatePosts () {
  $.ajax({
    dataType: 'json',
    data: { bid: GET('id') },
    url: '/xhr.php?f=getPinPost',
    success: function (data) {
      if (data.status == 1) {
        $('#pinnwand .posts').html(data.html)
      }
    }
  })
}

export function u_delPost (id) {
  $('#delete_shure').data('pid', id).dialog('open')
}

export function u_betrieb_sign_out (bid) {
  $('#signout_shure').dialog('open')
}

export function acceptRequest (fsid, bid) {
  showLoader()
  $.ajax({
    dataType: 'json',
    data: 'fsid=' + fsid + '&bid=' + bid,
    url: '/xhr.php?f=acceptRequest',
    success: function (data) {
      if (data.status == 1) {
        reload()
      }
    },
    complete: function () { hideLoader() }
  })
}
export function warteRequest (fsid, bid) {
  showLoader()
  $.ajax({
    dataType: 'json',
    data: 'fsid=' + fsid + '&bid=' + bid,
    url: '/xhr.php?f=warteRequest',
    success: function (data) {
      if (data.status == 1) {
        reload()
      }
    },
    complete: function () { hideLoader() }
  })
}
export function denyRequest (fsid, bid) {
  showLoader()
  $.ajax({
    dataType: 'json',
    data: 'fsid=' + fsid + '&bid=' + bid,
    url: '/xhr.php?f=denyRequest',
    success: function (data) {
      if (data.status == 1) {
        reload()
      }
    },
    complete: function () { hideLoader() }
  })
}
