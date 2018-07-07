/* eslint-disable eqeqeq */
import '@/core'
import '@/globals'
import $ from 'jquery'
import {
  ajax,
  goTo,
  GET,
  showLoader,
  hideLoader,
  reload
} from '@/script'
import i18n from '@/i18n'
import './Region.css'
import * as wall from '@/wall'
import { expose } from '@/utils'
import { vueUse } from '@/vue'
import ThreadPost from './components/ThreadPost'

vueUse({
  ThreadPost
})

expose({
  unfollow,
  follow,
  unstick,
  stick
})

$('a[href=\'#signout\']').click(function () {
  $('#signout_sure').dialog('open')
  return false
})

$('#signout_sure').dialog({
  autoOpen: false,
  modal: true,
  buttons: [
    {
      text: i18n('button.yes_i_am_sure'),
      click: function () {
        ajax.req('bezirk', 'signout', {
          data: $('input', this).serialize(),
          success: function () {
            goTo('/?page=relogin&url=' + encodeURIComponent('/?page=dashboard'))
          }
        })
      }
    },
    {
      text: i18n('button.abort'),
      click: function () {
        $(this).dialog('close')
      }
    }
  ]
})

if (GET('sub') == 'wall') {
  wall.init('bezirk', GET('bid'))
}

if (['botforum', 'forum'].includes(GET('sub'))) {
  let loadedPages = []
  $(window).scroll(function () {
    if ($(window).scrollTop() < $(document).height() - $(window).height() - 10) {
      return
    }

    var page = parseInt($('#morebutton').val()) || 1
    for (let i = 0; i < loadedPages.length; i++) {
      if (loadedPages[i] == page) {
        return
      }
    }
    loadedPages.push(page)
    let last = $('.thread:last').attr('id')
    if (last != undefined) {
      ajax.req('bezirk', 'morethemes', {
        data: {
          bid: GET('bid'),
          bot: GET('sub') == 'botforum' ? 1 : 0,
          page: page,
          last: last.split('-')[1]
        },
        success: function (data) {
          $('#morebutton').val(page + 1)
          $('.forum_threads.linklist').append(data.html)
        }
      })
    }
  })

  let clickedPid = null
  $('.bt_delete').click(function (el) {
    clickedPid = parseInt($(this).attr('href').replace('#p', ''))
    $('#delete_sure').dialog('open')
  })

  $('#delete_sure').dialog({
    autoOpen: false,
    modal: true,
    buttons: [
      {
        text: i18n('button.yes_i_am_sure'),
        click: function () {
          showLoader()
          $.ajax({
            url: 'xhr.php?f=delPost',
            data: { 'pid': clickedPid },
            success: function (ret) {
              if (ret == 1) {
                $('#tpost-' + clickedPid).remove()
                if ($('.post').length == 0) {
                  reload()
                } else {
                  $('#delete_sure').dialog('close')
                }
              }
            },
            complete: function () {
              hideLoader()
            }
          })
        }
      },
      {
        text: i18n('button.abort'),
        click: function () {
          $('#delete_sure').dialog('close')
        }
      }
    ]
  })
}

function handleButtonRequest (module, method, data, buttonSelector) {
  let buttons = $(buttonSelector)
  buttons.prop('disable', true)
  ajax.req(module, method, {
    data: data,
    success: () => {
      if (buttons) {
        buttons.remove()
      }
    }
  })
}

export function unfollow (tid, bid) {
  handleButtonRequest('bezirk', 'unfollowTheme', { tid: tid, bid: bid }, '.bt_unfollow')
  return false
}

export function follow (tid, bid) {
  handleButtonRequest('bezirk', 'followTheme', { tid: tid, bid: bid }, '.bt_follow')
  return false
}

export function unstick (tid, bid) {
  handleButtonRequest('bezirk', 'unstickTheme', { tid: tid, bid: bid }, '.bt_unstick')
  return false
}

export function stick (tid, bid) {
  handleButtonRequest('bezirk', 'stickTheme', { tid: tid, bid: bid }, '.bt_stick')
  return false
}
