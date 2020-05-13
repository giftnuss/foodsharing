/* eslint-disable eqeqeq */

/*
 * This is mostly only relevant for the Message module, but some of the functions are called from elsewhere
 * (after checking the current page), so this could probably be split into two.
 */
import $ from 'jquery'
import conv from '@/conv'
import i18n from '@/i18n'
import serverData from '@/server-data'
import autosize from 'autosize'
import timeformat from '@/timeformat'
import * as api from '@/api/conversations'
import conversationStore from '@/stores/conversations'
import profileStore from '@/stores/profiles'

import {
  img,
  GET,
  pulseInfo,
  pulseError,
  shuffle
} from '@/script'

import {
  dateDistanceInWords, dateFormat,
  plainToHtml,
  plainToHtmlAttribute
} from '@/utils'

const msg = {
  conversation_id: 0,
  last_message_id: 0,
  fsid: 0,
  listTimeout: false,
  moreIsLoading: false,
  firstMessageReached: false,
  $conversation: null,
  $answer: null,
  $submit: null,
  $convs: null,

  init: function () {
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
      }
    })

    autosize(document.getElementById('msg_answer'))

    msg.$answer.on('resize', function () {
      msg.$answer.css('margin-top', `-${msg.$answer.height() - 40}px`)
    })

    msg.$answer.on('focus', function () {
      conversationStore.markAsRead(msg.conversation_id)
    })

    /*
     * initiate message submit functionality for conversation form
     */
    $('#msg-control form').on('submit', async function (ev) {
      ev.preventDefault()

      var val = msg.$answer.val()
      if (val != '') {
        msg.$answer.val('')
        msg.$answer.css('height', '40px')
        msg.$answer[0].focus()
        msg.showLoader()
        try {
          await api.sendMessage(msg.conversation_id, val)
        } catch (e) {
          pulseError(i18n('chat.error_sending_message'))
          console.error(e)
        } finally {
          setTimeout(function () {
            msg.hideLoader()
          }, 100)
        }
      }
    })

    var cid = 0
    var gcid = GET('cid')
    if (GET('cid') != undefined && parseInt(gcid) > 0) {
      cid = gcid
      this.loadConversation(cid)
    } else if (GET('u2c') != undefined) {
      conv.userChat(parseInt(GET('u2c')))
    }
  },

  isMob: function () {
    return $(window).width() <= 600
  },

  /*
   * Method for arrived message data from socket.io
   */
  push: function (message) {
    if (message.cid == msg.conversation_id) {
      msg.appendMsg(message.message)
      msg.scrollBottom()
    }
    msg.updateConvList(message)
  },

  updateConvList: function (message) {
    const $item = $(`#convlist-${message.cid}`)
    const $itemLink = $item.children('a')
    if ($item.length > 0) {
      $itemLink.children('.msg').text(message.message.body)
      $itemLink.children('.time').text(dateDistanceInWords(message.message.sentAt))
      $item.hide()
      $item.prependTo('#conversation-list ul:first')
      $item.show('highlight', { color: '#F5F5B5' })
    }
  },

  initComposer: function () {
    autosize(document.getElementById('compose_body'))
    $('#compose_submit').on('click', async function (ev) {
      ev.preventDefault()

      const recip = msg.getRecipients()
      if (recip != false) {
        const body = $('#compose_body').val()
        if (body != '') {
          try {
            const conversation = await api.createConversation(recip)
            await api.sendMessage(conversation.conversation.id, body)
            msg.clearComposeForm()
            msg.loadConversation(conversation.conversation.id)
          } catch (e) {
            pulseError(i18n('chat.error_sending_message'))
            console.error(e)
          }
        } else {
          pulseInfo(i18n('chat.empty_message'))
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
    const $el = msg.msgTpl(message)

    if (msg.$conversation == undefined) {
      msg.$conversation = $('#msg-conversation')
    }

    msg.$conversation.children('ul:first').prepend($el)

    $el.show('highlight', { color: '#F5F5B5' })
  },

  appendMsg: function (message) {
    const $el = msg.msgTpl(message)

    if (msg.$conversation == undefined) {
      msg.$conversation = $('#msg-conversation')
    }

    msg.$conversation.children('ul:first').append($el)

    $el.show('highlight', { color: '#F5F5B5' })

    this.last_message_id = message.id
  },

  msgTpl: function (message) {
    /*
     * set a class 'my-message' to active user's own messages
     */
    let ownMessageClass = ''
    const author = profileStore.profiles[message.authorId]
    if (message.authorId === serverData.user.id) { ownMessageClass = ' class="my-message" ' }
    return $(`<li id="msg-${message.id}" ${ownMessageClass} style="display:none;"><span class="img"><a title="${plainToHtmlAttribute(author.name)}" href="/profile/${message.authorId}"><img height="35" src="${img(author.avatar, 'mini')}" /></a></span><span class="body">${plainToHtml(message.body)}<span class="time">${dateFormat(message.sentAt)}</span></span><span class="clear"></span></li>`)
  },

  getRecipients: function () {
    const out = []
    $('#compose_recipients li.tagedit-listelement-old input').each(function () {
      let id = $(this).attr('name').replace('compose_recipients[', '').split('-')[0]
      id = parseInt(id)
      out[out.length] = id
    })

    if (out.length > 0) {
      return out
    } else {
      pulseError(i18n('chat.empty_recipients'))
      return false
    }
  },
  compose: function () {
    document.getElementById('compose').style.display = ''
    document.getElementById('right').classList.remove('list-active')
    document.getElementById('msg-conversation-wrapper').style.display = 'none'
    $('#conversation-list .active').removeClass('active')
    msg.conversation_id = 0
    msg.last_message_id = 0
  },
  loadConversation: async function (id, reload = false) {
    if (id == msg.conversation_id && !reload) {
      msg.scrollBottom()
      msg.$answer.trigger('select')
      return false
    }
    msg.conversation_id = id

    await conversationStore.loadConversation(id)
    const conversation = conversationStore.conversations[id]

    msg.resetConversation()

    const $conversation = $('#msg-conversation ul:first')
    $conversation.html('')

    const otherMembers = conversation.members.filter(m => m != msg.fsid)

    const titleText = conversation.title || `Unterhaltung mit ${otherMembers.map(member => profileStore.profiles[member].name).join(', ')}`

    const title = `
      &nbsp;<div class="images">
        ${otherMembers.map(member => `
          <a title="${plainToHtmlAttribute(profileStore.profiles[member].name)}" href="/profile/${profileStore.profiles[member].id}">
            <img src="${img(profileStore.profiles[member].avatar, 'mini')}" width="22" alt="${plainToHtmlAttribute(profileStore.profiles[member].name)}" />
          </a>
        `).slice(0, 25).join('')}
      </div>
      ${plainToHtml(titleText)}
      <div style="clear:both;"></div>
    `

    $('#msg-conversation-title a').remove()
    $('#msg-conversation-title').append(title)

    /*
     * append messages to conversation message list
     */
    Object.values(conversation.messages).forEach(m => msg.appendMsg(m))

    document.getElementById('compose').style.display = 'none'
    document.getElementById('right').classList.add('list-active')
    document.getElementById('msg-conversation-wrapper').style.display = ''
    msg.scrollBottom()

    msg.$convs.children('li.active').removeClass('active')
    $(`#convlist-${id}`).addClass('active')

    msg.$answer.trigger('select')

    msg.scrollTrigger()
    msg.firstMessageReached = false
  },

  loadMore: async function () {
    const lmid = parseInt($('#msg-conversation li:first').attr('id').replace('msg-', ''))

    if (!msg.moreIsLoading && !msg.firstMessageReached) {
      msg.moreIsLoading = true
      try {
        const cnt = await conversationStore.loadMoreMessages(msg.conversation_id)

        const messages = Object.values(conversationStore.conversations[msg.conversation_id].messages)
        for (let i = cnt - 1; i >= 0; i--) {
          msg.prependMsg(messages[i])
        }

        if (cnt === 0) {
          msg.firstMessageReached = true
        }

        const position = $(`#msg-${lmid}`).position()
        if (!position) return

        if (!msg.isMob()) {
          msg.$conversation.slimScroll({ scrollTo: `${position.top}px` })
        } else {
          $(window).scrollTop(position.top)
        }
      } catch (e) {
        pulseError(i18n('chat.error_loading_messages'))
        console.error(e)
      } finally {
        msg.moreIsLoading = false
      }
    }
  },

  scrollTrigger: function () {
    const fun = function () {
      const $conv = $(this)
      if ($conv.scrollTop() == 0) {
        msg.loadMore()
      }
    }
    if (!msg.isMob()) {
      msg.$conversation.off('scroll')
      msg.$conversation.on('scroll', fun)
    } else {
      $(window).off('scroll')
      $(window).on('scroll', fun)
    }
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

    const $el = $(`<li style="display:none;" id="convlist-${conversation.id}"${cssclass}><a href="#" onclick="msg.loadConversation(${conversation.id});return false;"><span class="pics">${pics}</span><span class="names">${plainToHtml(names)}</span><span class="msg">${plainToHtml(conversation.body)}</span><span class="time">${timeformat.nice(conversation.time)}</span><span class="clear"></span></a></li>`)

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

export default msg
