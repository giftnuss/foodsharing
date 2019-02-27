/* eslint-disable eqeqeq */

/*
 * This is mostly only relevant for the Message module, but some of the functions are called from elsewhere
 * (after checking the current page), so this could probably be split into two.
 */
import $ from 'jquery'
import info from '@/info'
import conv from '@/conv'
import autoLink from '@/autoLink'
import autosize from 'autosize'
import timeformat from '@/timeformat'
import * as api from '@/api/conversations'
import conversationStore from '@/stores/conversations'

import {
  ajax,
  stopHeartbeats,
  img,
  GET,
  pulseInfo,
  pulseError,
  shuffle,
  nl2br
} from '@/script'

const msg = {
  conversation_id: 0,
  last_message_id: 0,
  heartbeatTime: 500,
  fsid: 0,
  heartbeatXhr: false,
  listTimeout: false,
  moreIsLoading: false,
  $conversation: null,
  $answer: null,
  $submit: null,
  $convs: null,

  init: function () {
    /*
     * to reduce server load, stop all other heartbeat functionality
     */
    stopHeartbeats()

    /*
     * initiate dom querys for a little bit js performance
     */
    this.$conversation = $('#msg-conversation')
    this.$answer = $('#msg_answer')
    this.$submit = $('#msg-control input[type=submit]')
    this.$convs = $('#conversation-list ul')

    /*
     * call method to initiate the compose message functionality
     */
    this.initComposer()

    if (!msg.isMob()) {
      var height = `${$(window).height() - 200}px`
      this.$conversation.css('height', height)

      this.$conversation.slimScroll({
        height: height
      })
    } else {
      this.$conversation.css({
        height: 'auto',
        overflow: 'hidden',
        padding: '0',
        margin: '0'
      })
      msg.scrollBottom()
    }

    /*
     * make the message windows as big as possible
     */
    $(window).on('resize', function () {
      if (!msg.isMob()) {
        var height = `${$(window).height() - 200}px`
        msg.$conversation.css('height', height)
        msg.$conversation.parent('.slimScrollDiv').css('height', height)
        msg.$conversation.slimScroll({
          height: height,
          scrollTo: `${msg.$conversation.prop('scrollHeight')}px`
        })
      } else {
        /* THIS CODE IS BROKEN BECAUSE app.resize does not exist, it's a copy-and-paste from stackoverflow error
        // resize event is triggered also on scrolling in android / ios
        // http://stackoverflow.com/questions/14257541/android-browser-triggers-jquery-window-resize-on-scolling
        clearTimeout(app.resize.timer)
        app.resize.timer = setTimeout(function () {
          // do not check height, because it changes on scrolling due to hide / show address bar
          let windowChanged = $(window).width() != app.size.window_width
          if (windowChanged) {

            // window was actually resized
            msg.scrollBottom()

          }
        }, 500)
        */
      }
    })

    autosize(document.getElementById('msg_answer'))

    msg.$answer.on('resize', function () {
      msg.$answer.css('margin-top', `-${msg.$answer.height() - 40}px`)
    })

    /*
     * initiate message submit functionality for conversation form
     */
    $('#msg-control form').on('submit', function (ev) {
      ev.preventDefault()

      var val = msg.$answer.val()
      if (val != '') {
        msg.$answer.val('')
        msg.$answer.css('height', '40px')
        msg.$answer[0].focus()
        msg.showLoader()

        ajax.req('msg', 'sendmsg', {
          loader: false,
          method: 'post',
          data: {
            c: msg.conversation_id,
            b: val
          },
          complete: function () {
            msg.hideLoader()
            setTimeout(function () {
              msg.hideLoader()
            }, 100)

            // reload conversations
            conversationStore.loadConversations()
          }

        })
      }
    })

    /*
     * if the conversation list is not empty we want to load the first one
     */

    var cid = 0
    var gcid = GET('cid')
    if (GET('cid') != undefined && parseInt(gcid) > 0) {
      cid = gcid
      this.loadConversation(cid)
    } else if (GET('u2c') != undefined) {
      conv.userChat(parseInt(GET('u2c')))
    } else if ($('#conversation-list ul li a').length > 0) {
      cid = $('#conversation-list ul li:first').attr('id').split('-')[1]
      this.loadConversation(cid)
    } else {
      msg.heartbeat()
    }
  },

  isMob: function () {
    return $(window).width() <= 600
  },

  /**
   * list heartbeat checks every time updates on all conversations
   */
  heartbeat: function () {
    info.editService('msg', 'heartbeat', {
      cid: msg.conversation_id,
      mid: msg.last_message_id,
      speed: 'fast'
    })
  },

  /*
   * Method for arrived message data from socket.io
   */
  push: function (message) {
    if (message.cid == msg.conversation_id) {
      msg.appendMsg(message)
      msg.scrollBottom()
    } else {
      msg.updateConvList(message)
    }
    conversationStore.loadConversations()
  },

  updateConvList: function (message) {
    const $item = $(`#convlist-${message.cid}`)
    const $itemLink = $item.children('a')
    if ($item.length > 0) {
      $itemLink.children('.msg').html(message.body)
      $itemLink.children('.time').text(timeformat.nice(message.time))
      $item.hide()
      $item.prependTo('#conversation-list ul:first')
      $item.show('highlight', { color: '#F5F5B5' })
    } else {
      msg.loadConversationList()
    }
  },

  /**
   * Method will be called if there arrived something new from the server
   */
  pushArrived: function (data) {
    let ret = data.msg_heartbeat

    console.log(ret._duration)

    /*
     * update current chat if there are new messages
     */
    if (ret.messages != undefined) {
      for (var i = 0; i < ret.messages.length; i++) {
        msg.appendMsg(ret.messages[i])
      }
      msg.scrollBottom()
    }

    /*
     * update conversation list move newest on top etc.
     */
    if (ret.convs) {
      for (let i = 0; i < ret.convs.length; i++) {
        // if the element exist remove to add it new on the top
        $(`#convlist-${ret.convs[i].id}`).remove()
        msg.appendConvList(ret.convs[i], true)
      }
    }
  },

  /**
   * function will abort the heartbeat ajax call and restart it
   */
  heartbeatRestart: function () {
    info.editService('msg', 'heartbeat', {
      cid: msg.conversation_id,
      mid: msg.last_message_id,
      speed: 'fast'
    })
  },
  initComposer: function () {
    autosize(document.getElementById('compose_body'))
    $('#compose_submit').on('click', function (ev) {
      ev.preventDefault()

      let recip = msg.getRecipients()
      if (recip != false) {
        let body = $('#compose_body').val()
        if (body != '') {
          ajax.req('msg', 'newconversation', {
            data: {
              recip: recip,
              body: body
            },
            method: 'post',
            success: function (data) {
              msg.clearComposeForm()
              msg.loadConversation(data.cid)
            }
          })
        } else {
          pulseInfo('Du musst eine Nachricht eingeben')
        }
      }
    })
  },
  showLoader: function () {
    this.$conversation.children('.loader').show()
    this.scrollBottom()
  },
  hideLoader: function () {
    this.$conversation.children('.loader').hide()
  },

  prependMsg: function (message) {
    let $el = msg.msgTpl(message)

    if (msg.$conversation == undefined) {
      msg.$conversation = $('#msg-conversation')
    }

    msg.$conversation.children('ul:first').prepend($el)

    $el.show('highlight', { color: '#F5F5B5' })
  },

  appendMsg: function (message) {
    let $el = msg.msgTpl(message)

    if (msg.$conversation == undefined) {
      msg.$conversation = $('#msg-conversation')
    }

    msg.$conversation.children('ul:first').append($el)

    $el.show('highlight', { color: '#F5F5B5' })

    this.last_message_id = message.id
  },

  msgTpl: function (message) {
    return $(`<li id="msg-${message.id}" style="display:none;"><span class="img"><a title="${message.fs_name}" href="/profile/${message.fs_id}"><img height="35" src="${img(message.fs_photo, 'mini')}" /></a></span><span class="body">${nl2br(autoLink(message.body))}<span class="time">${timeformat.nice(message.time)}</span></span><span class="clear"></span></li>`)
  },

  getRecipients: function () {
    let out = []
    $('#compose_recipients li.tagedit-listelement-old input').each(function () {
      let id = $(this).attr('name').replace('compose_recipients[', '').split('-')[0]
      id = parseInt(id)
      out[out.length] = id
    })

    console.log(out)

    if (out.length > 0) {
      return out
    } else {
      pulseError('Du hast noch keine Empfänger ausgewählt.')
      return false
    }
  },
  compose: function () {
    document.getElementById('compose').style.display = ''
    document.getElementById('msg-conversation-wrapper').style.display = 'none'
    $('#conversation-list .active').removeClass('active')
    msg.conversation_id = 0
    msg.last_message_id = 0
  },
  loadConversation: async function (id) {
    if (id == msg.conversation_id) {
      msg.scrollBottom()
      msg.$answer.trigger('select')
      return false
    }
    msg.conversation_id = id

    const { name, members, messages } = await api.getConversation(id)

    msg.resetConversation()

    const $conversation = $('#msg-conversation ul:first')
    $conversation.html('')

    const otherMembers = members.filter(m => m.id != msg.fsid)

    const titleText = name || `Unterhaltung mit ${otherMembers.map(member => member.name).join(', ')}`

    const title = `
      &nbsp;<div class="images">
        ${otherMembers.map(member => `
          <a title="${member.name}" href="/profile/${member.id}">
            <img src="${img(member.avatar, 'mini')}" width="22" alt="${member.name}" />
          </a>
        `).join('')}  
      </div>
      ${titleText}
      <div style="clear:both;"></div>
    `

    $('#msg-conversation-title a').remove()
    $('#msg-conversation-title').append(title)

    /*
     * append messages to conversation message list
     */
    if (messages) {
      messages
        .reverse()
        .forEach(m => msg.appendMsg(m))
    }

    document.getElementById('compose').style.display = 'none'
    document.getElementById('msg-conversation-wrapper').style.display = ''
    msg.scrollBottom()

    msg.$convs.children('li.active').removeClass('active')
    $(`#convlist-${id}`).addClass('active')

    msg.$answer.trigger('select')

    msg.heartbeatRestart()

    msg.scrollTrigger()
  },

  loadMore: function () {
    let lmid = parseInt($('#msg-conversation li:first').attr('id').replace('msg-', ''))

    if (!msg.moreIsLoading) {
      msg.moreIsLoading = true
      ajax.req('msg', 'loadmore', {
        loader: true,
        data: {
          lmid: lmid,
          cid: msg.conversation_id
        },
        success: function (ret) {
          msg.moreIsLoading = false

          for (let i = 0; i < ret.messages.length; i++) {
            msg.prependMsg(ret.messages[i])
          }

          let position = $(`#msg-${lmid}`).position()

          if (!msg.isMob()) {
            msg.$conversation.slimScroll({ scrollTo: `${position.top}px` })
          } else {
            $(window).scrollTop(position.top)
          }
        }
      })
    }
  },

  scrollTrigger: function () {
    msg.moreIsLoading = false

    if (!msg.isMob()) {
      msg.$conversation.off('scroll')
      msg.$conversation.on('scroll', function () {
        let $conv = $(this)
        if ($conv.scrollTop() == 0) {
          msg.loadMore()
        }
      })
    } else {
      $(window).off('scroll')
      $(window).on('scroll', function () {
        let $conv = $(this)

        if ($conv.scrollTop() == 0) {
          msg.loadMore()
        }
      })
    }
  },

  loadConversationList: function () {
    ajax.req('msg', 'loadconvlist', {
      loader: false,
      success: function (ret) {
        if (ret.convs != undefined && ret.convs.length > 0) {
          msg.$convs.html('')
          msg.loadConversation(ret.convs[0].id)

          for (var i = 0; i < ret.convs.length; i++) {
            msg.appendConvList(ret.convs[i])
          }
        }
      },
      complete: function () {

      }
    })
  },
  resetConversation: function () {
    $('#msg-conversation-title').html('<i class="fas fa-comment"></i>')
    $('#msg-conversation ul').html('')
  },
  appendConvList: function (conversation, prepend) {
    let pics = ''
    let names = ''
    if (conversation.member != undefined && conversation.member.length > 0) {
      let picwidth = 50
      let size = 'med'
      if (conversation.member.length > 2) {
        conversation.member = shuffle(conversation.member)
        picwidth = 25
        size = 'mini'
      }

      for (var y = 0; y < conversation.member.length; y++) {
        if (msg.fsid != conversation.member[y].id) {
          pics += `<img width="${picwidth}" src="${img(conversation.member[y].photo, size)}" />`
          names += `, ${conversation.member[y].name}`
        }
      }
    }

    names = names.substring(2)
    let cssclass = ''

    if (msg.conversation_id == conversation.id) {
      cssclass = ' class="active"'
    }

    const $el = $(`<li style="display:none;" id="convlist-${conversation.id}"${cssclass}><a href="#" onclick="msg.loadConversation(${conversation.id});return false;"><span class="pics">${pics}</span><span class="names">${names}</span><span class="msg">${conversation.body}</span><span class="time">${timeformat.nice(conversation.time)}</span><span class="clear"></span></a></li>`)

    if (prepend != undefined) {
      msg.$convs.prepend($el)
    } else {
      msg.$convs.append($el)
    }

    $el.show('highlight', { color: '#F5F5B5' })

    msg.$convs.children('.noconv').remove()
  },
  clearComposeForm: function () {
    $('#compose_recipients-wrapper .tagedit-listelement-old').remove()
    $('#compose_body').val('')
  },
  scrollBottom: function () {
    if (!msg.isMob()) {
      msg.$conversation.slimScroll({ scrollTo: `${msg.$conversation.prop('scrollHeight')}px` })
    } else {
      $(window).scrollTop($(document).height())
    }
  }
}

/* should only initialize it in Message.js when it is webpack'd
$(function () {
  msg.init()
})
*/

export default msg
