/* eslint-disable camelcase,eqeqeq */

import $ from 'jquery'

import { hideLoader, showLoader, reload, chat, ajreq, profile } from '@/script'

import { store } from '@/server-data'

export function u_updatePosts () {
  $.ajax({
    dataType: 'json',
    data: $('div#pinnwand form').serialize(),
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

export function u_contextAction (action, fsid) {
  if (action === 'gotoprofile') {
    profile(fsid)
  } else if (action === 'message') {
    chat(fsid)
  } else if (action === 'report') {
    ajreq('reportDialog', { app: 'report', fsid: fsid, bid: store.id })
  } else {
    showLoader()
    $.ajax({
      url: '/xhr.php?f=bcontext',
      data: { action: action, fsid: fsid, bid: store.id, bzid: store.bezirk_id },
      dataType: 'json',
      success: function (data) {
        if (data.status == 1) {
          if (action === 'toteam') {
            $('.fs-' + fsid).removeClass('jumper')
            $('.fs-' + fsid).addClass('team')
          } else if (action === 'tojumper') {
            $('.fs-' + fsid).removeClass('team')
            $('.fs-' + fsid).addClass('jumper')
          } else if (action === 'delete') {
            $('.fs-' + fsid).remove()
          }
        }
      },
      complete: function () {
        hideLoader()
      }
    })
  }
}

export function createJumperMenu () {
  return {
    callback: function (key, options) {
      var li = $(this).parent()

      const fsid = li.attr('class').split('fs-')[1]

      u_contextAction(key, fsid)
    },
    items: {
      gotoprofile: { name: 'Profil anzeigen', icon: 'fas fa-user fa-fw' },
      report: { name: 'Melden', icon: 'fas fa-bullhorn fa-fw' },
      delete: { name: 'Aus Team löschen', icon: 'fas fa-user-times fa-fw' },
      toteam: { name: 'Ins Team aufnehmen', icon: 'fas fa-clipboard-check fa-fw' },
      message: { name: 'Nachricht schreiben', icon: 'fas fa-comment fa-fw' }
    }
  }
}

export function createMenu () {
  return {
    callback: function (key, options) {
      var li = $(this).parent()

      const fsid = li.attr('class').split('fs-')[1]

      u_contextAction(key, fsid)
    },
    items: {
      gotoprofile: { name: 'Profil anzeigen', icon: 'fas fa-user fa-fw' },
      report: { name: 'Melden', icon: 'fas fa-bullhorn fa-fw' },
      delete: { name: 'Aus Team löschen', icon: 'fas fa-user-times fa-fw' },
      tojumper: { name: 'Auf die Springerliste', icon: 'fas fa-mug-hot fa-fw' },
      message: { name: 'Nachricht schreiben', icon: 'fas fa-comment fa-fw' }
    }
  }
}

export function addContextMenu (selector, offsetY, build) {
  $(selector).on('click', function () {
    const $this = $(this)
    const offset = $this.offset()
    $this.contextMenu({
      x: offset.left - 42,
      y: offset.top - offsetY
    })
  })
  $.contextMenu({ selector, trigger: 'none', build })
}
