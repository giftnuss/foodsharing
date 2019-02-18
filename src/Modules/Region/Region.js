/* eslint-disable eqeqeq */
import '@/core'
import '@/globals'
import $ from 'jquery'
import {
  ajax,
  goTo,
  GET
} from '@/script'
import i18n from '@/i18n'
import './Region.css'
import * as wall from '@/wall'
import { vueRegister, vueApply } from '@/vue'
import Thread from './components/Thread'
import MemberList from './components/MemberList'

$('a[href=\'#signout\']').on('click', function () {
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
            goTo(`/?page=relogin&url=${encodeURIComponent('/?page=dashboard')}`)
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
} else if (GET('sub') === 'members') {
  vueRegister({
    MemberList
  })
  vueApply('#vue-memberlist')
} else if (['botforum', 'forum'].includes(GET('sub'))) {
  if (GET('tid') !== 'undefined') {
    vueRegister({
      Thread
    })
    vueApply('#vue-thread')
  } else {
    let loadedPages = []
    $(window).on('scroll', function () {
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
  }
}
