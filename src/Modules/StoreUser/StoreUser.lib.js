/* eslint-disable camelcase,eqeqeq */

import $ from 'jquery'

import i18n from '@/i18n'
import { hideLoader, showLoader, reload, chat, ajreq, profile } from '@/script'

import { store } from '@/server-data'

export function u_clearDialogs () {
  $('.datefetch').val('')
  $('.shure_date').show()
  $('.shure_range_date').hide()
  $('.rangeFetch').hide()
  $('button').show()
}

export function u_updatePosts () {
  $.ajax({
    dataType: 'json',
    data: $('div#pinnwand form').serialize(),
    url: 'xhr.php?f=getPinPost',
    success: function (data) {
      if (data.status == 1) {
        $('#pinnwand .posts').html(data.html)
      }
    }
  })
}

export function u_undate (date, dateFormat) {
  $('#u_undate').dialog('option', 'title', i18n('del_date_for') + ' ' + dateFormat)

  $('#team_msg-wrapper').hide()
  $('#have_backup').show()
  $('#msg_to_team').show()
  $('#send_msg_to_team').hide()

  $('#undate-date').val(date)
  $('#u_undate').dialog('open')

  $('#team_msg').val(i18n('tpl_msg_to_team', {
    BETRIEB: store.name,
    DATE: dateFormat
  }))
}

export function u_delPost (id) {
  $('#delete_shure').data('pid', id).dialog('open')
}

export function u_betrieb_sign_out (bid) {
  $('#signout_shure').dialog('open')
}

export function u_fetchconfirm (fsid, date, el) {
  var item = $(el)
  showLoader()
  $.ajax({
    url: 'xhr.php?f=fetchConfirm',
    data: {
      fsid: parseInt(fsid),
      bid: store.id,
      date: date
    },
    success: function (ret) {
      if (ret == 1) {
        item.parent().removeClass('unconfirmed')
      }
    },
    complete: function () {
      hideLoader()
    }
  })
}

export function u_fetchdeny (fsid, date, el) {
  var item = $(el)
  showLoader()
  $.ajax({
    url: 'xhr.php?f=fetchDeny',
    data: {
      fsid: parseInt(fsid),
      bid: store.id,
      date: date
    },
    success: function (ret) {
      if (ret == 1) {
        item.parent().parent().append('<li class="filled empty timedialog-add-me"><a onclick="return false;" href="#"><img alt="nobody" src="/img/nobody.gif"></a></li>')
        item.parent().remove()
      }
    },
    complete: function () {
      hideLoader()
    }
  })
}

export function acceptRequest (fsid, bid) {
  showLoader()
  $.ajax({
    dataType: 'json',
    data: 'fsid=' + fsid + '&bid=' + bid,
    url: 'xhr.php?f=acceptRequest',
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
    url: 'xhr.php?f=warteRequest',
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
    url: 'xhr.php?f=denyRequest',
    success: function (data) {
      if (data.status == 1) {
        reload()
      }
    },
    complete: function () { hideLoader() }
  })
}

export function u_contextAction (action, fsid) {
  if (action == 'gotoprofile') {
    profile(fsid)
  } else if (action == 'message') {
    chat(fsid)
  } else if (action == 'report') {
    ajreq('reportDialog', { app: 'report', fsid: fsid, bid: store.id })
  } else {
    showLoader()
    $.ajax({
      url: 'xhr.php?f=bcontext',
      data: { 'action': action, 'fsid': fsid, 'bid': store.id, 'bzid': store.bezirk_id },
      dataType: 'json',
      success: function (data) {
        if (data.status == 1) {
          if (action == 'toteam') {
            $('.fs-' + fsid).removeClass('jumper')
            $('.fs-' + fsid).addClass('team')
          } else if (action == 'tojumper') {
            $('.fs-' + fsid).removeClass('team')
            $('.fs-' + fsid).addClass('jumper')
          } else if (action == 'delete') {
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
      'gotoprofile': { name: 'Profil anzeigen', icon: 'fas fa-user' },
      'report': { name: 'Melden', icon: 'fas fa-bullhorn' },
      'delete': { name: 'Aus Team löschen', icon: 'fas fa-user-times' },
      'toteam': { name: 'Ins Team aufnehmen', icon: 'fas fa-clipboard-check' },
      'message': { name: 'Nachricht schreiben', icon: 'fas fa-comment' }
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
      'gotoprofile': { name: 'Profil anzeigen', icon: 'fas fa-user' },
      'report': { name: 'Melden', icon: 'fas fa-bullhorn' },
      'delete': { name: 'Aus Team löschen', icon: 'fas fa-user-times' },
      'tojumper': { name: 'Auf die Springerliste', icon: 'fas fa-mug-hot' },
      'message': { name: 'Nachricht schreiben', icon: 'fas fa-comment' }
    }
  }
}

export function u_timetableAction (key, el) {
  const val = $(el).children('input:first').val().split(':::')

  if (key == 'confirm') {
    u_fetchconfirm(val[0], val[1], el)
  } else if (key == 'deny') {
    u_fetchdeny(val[0], val[1], el)
  } else if (key == 'message') {
    chat(val[0])
  }
}

export function createConfirmedMenu () {
  return {
    callback: function (key, options) {
      u_timetableAction(key, this)
    },
    items: {
      'gotoprofile': { name: 'Profil anzeigen', icon: 'fas fa-user' },
      'deny': { name: 'Austragen', icon: 'fas fa-calendar-times' },
      'message': { name: 'Nachricht schreiben', icon: 'fas fa-comment' }
    }
  }
}

export function createUnconfirmedMenu () {
  return {
    callback: function (key, options) {
      u_timetableAction(key, this)
    },
    items: {
      'gotoprofile': { name: 'Profil anzeigen', icon: 'fas fa-user' },
      'confirm': { name: 'Bestätigen', icon: 'fas fa-check' },
      'deny': { name: 'Austragen', icon: 'fas fa-calendar-times' },
      'message': { name: 'Nachricht schreiben', icon: 'fas fa-comment' }
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
