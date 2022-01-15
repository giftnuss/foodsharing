/* eslint-disable eqeqeq */

import $ from 'jquery'

import storage from '@/storage'
import { GET, goTo, isMob, pulseError, img } from '@/script'
import serverData from '@/server-data'
import { dateFormat } from '@/./utils'
import msg from '@/msg'
import conversationStore from '@/stores/conversations'
import profileStore from '@/stores/profiles'
import * as api from '@/api/conversations'
import { url } from '@/urls'
import {
  plainToHtml,
} from '@/utils'

const conv = {

  initiated: false,

  chatboxes: null,

  /*
   * here we want catch all the chat dom elements
   */
  $chat: null,

  /*
   * current count of active chat message
   */
  chatCount: 0,

  /*
   * mark an active chatbox while writing
   */
  activeBox: 0,

  user2Conv: null,

  isBigPageMode: false,

  /*
   * init function have to be called one time on domready
   */
  init: function () {
    if (conv.initiated === false) {
      if (GET('page') === 'msg') {
        this.isBigPageMode = true
      }
      this.initiated = true
      this.chatboxes = []
      this.$chat = []
      this.user2Conv = []

      const chats = storage.get('msg-chats')

      if (chats != undefined) {
        for (let i = 0; i < chats.length; i++) {
          if (chats[i].id != undefined) {
            conv.appendChatbox(chats[i].id, chats[i].min)
          }
        }
      }
    }
  },
  userChat: async function (fsid) {
    if (!this.initiated) {
      this.init()
    }
    try {
      const conversation = await api.getConversationIdForConversationWithUser(fsid)
      conv.chat(conversation.id)
    } catch (e) {
      pulseError('Fehler beim Starten der Unterhaltung')
      console.error(e)
    }
  },

  getConvByFs: function (fsid) {
    for (let i = 0; i < conv.user2Conv.length; i++) {
      if (conv.user2Conv[i].fsid == fsid) {
        return conv.user2Conv[i].cid
      }
    }
    return false
  },

  chat: function (cid) {
    if (isMob()) {
      if (GET('page') == 'msg') {
        msg.loadConversation(cid)
      } else {
        goTo(`/?page=msg&cid=${cid}`)
      }
    } else {
      if (GET('page') == 'msg') {
        msg.loadConversation(cid)
      } else {
        if (!this.initiated) {
          this.init()
        }

        this.appendChatbox(cid)
      }
    }
  },

  storeOpenedChatWindows: function () {
    const ids = conv.getCids()

    if (ids.length > 0) {
      const infos = conv.getChatInfos()
      storage.set('msg-chats', infos)
    } else {
      storage.del('msg-chats')
    }
  },

  /**
   * push retrieve function on recieved data by polling will execute this here
   */
  push: function (data) {
    const key = conv.getKey(data.cid)
    if (key >= 0) {
      conv.maxbox(data.cid)
      conv.append(key, data.message)
      conv.scrollBottom(data.cid)
    }
  },

  // minimize or maximize the chatbox
  togglebox: function (cid) {
    const key = conv.getKey(cid)

    conv.chatboxes[key].el.children('.slimScrollDiv, .chatboxinput').toggle()
    if ($(`#chat-${cid} .chatboxinput`).is(':visible')) {
      conv.chatboxes[key].minimized = false
    } else {
      conv.chatboxes[key].minimized = true
    }

    conv.storeOpenedChatWindows()
  },

  // maximoze mini box
  maxbox: function (cid) {
    const key = conv.getKey(cid)
    conv.chatboxes[key].el.children('.slimScrollDiv, .chatboxinput').show()
    conv.chatboxes[key].minimized = false
  },

  // minimize a box
  minbox: function (cid) {
    const key = conv.getKey(cid)
    conv.chatboxes[key].el.children('.slimScrollDiv, .chatboxinput').hide()
    conv.chatboxes[key].minimized = true
  },

  checkInputKey: async function (event, chatboxtextarea, cid) {
    const $ta = $(chatboxtextarea)
    let val = $ta.val().trim()

    if (event.keyCode == 13 && event.shiftKey == 0 && val != '') {
      conv.showLoader(cid)

      setTimeout(function () {
        $ta.val('')
        $ta.css('height', '40px')
        $ta[0].focus()
      }, 100)

      // replace to many line breaks
      // eslint-disable-next-line no-control-regex
      val = val.replace(/(\n){3,}/gim, '\n\n')

      try {
        await api.sendMessage(cid, val)
      } catch (e) {
        pulseError('Fehler beim Senden der Nachricht')
        console.error(e)
      } finally {
        /* we intentionally don't reload conversation here as we will be updated via websocket */
        conv.hideLoader(cid)
      }
    }
  },

  /**
   * scroll to bottom after appending messages
   */
  scrollBottom: function (cid) {
    $(`#chat-${cid} .chatboxcontent`).slimScroll({ scrollTo: `${$('#chat-' + cid + ' .chatboxcontent').prop('scrollHeight')}px` })
  },

  /**
   * close the chatbox to thr given cid
   */
  close: function (cid) {
    const tmp = []
    let x = 0
    for (let i = 0; i < conv.chatboxes.length; i++) {
      if (conv.chatboxes[i].id == cid) {
        conv.chatboxes[i].el.remove()
      } else {
        conv.chatboxes[i].el.css('right', `${20 + (x * 285)}px`)
        tmp.push(conv.chatboxes[i])
        x++
      }
    }

    this.chatboxes = tmp

    this.chatCount--

    // re register polling service
    this.storeOpenedChatWindows()
  },

  showLoader: function (cid) {
    const key = this.getKey(cid)
    this.chatboxes[key].el.children('.chatboxhead').children('.chatboxtitle').children('i').removeClass('fa-comment fa-flip-horizontal').addClass('fa-spinner fa-spin')
  },

  hideLoader: function (cid) {
    const key = this.getKey(cid)
    if (key >= 0 && this.chatboxes[key] !== undefined) {
      this.chatboxes[key].el.children('.chatboxhead').children('.chatboxtitle').children('i').removeClass('fa-spinner fa-spin').addClass('fa-comment fa-flip-horizontal')
    }
  },

  /**
   * get the array key for given conversation_id
   */
  getKey: function (cid) {
    for (let i = 0; i < conv.chatboxes.length; i++) {
      if (conv.chatboxes[i].id == cid) {
        return i
      }
    }

    return -1
  },

  /**
   * get actic chatbox infos
   */
  getChatInfos: function () {
    const tmp = []

    for (let i = 0; i < conv.chatboxes.length; i++) {
      tmp.push({
        id: parseInt(conv.chatboxes[i].id),
        min: conv.chatboxes[i].minimized,
        lmid: conv.chatboxes[i].last_mid,
      })
    }

    return tmp
  },

  /**
   * get all conversation ids from active windows
   */
  getCids: function () {
    const tmp = []

    for (let i = 0; i < conv.chatboxes.length; i++) {
      tmp.push(parseInt(conv.chatboxes[i].id))
    }

    return tmp
  },

  /**
   * open settingsmenu to the given chatbox
   */
  settings: function (cid) {
    const key = this.getKey(cid)
    this.chatboxes[key].el.children('.chatboxhead').children('.settings').toggle()
  },

  /**
   * append an chat message to chat window with given array index attention not conversation id ;)
   */
  append: function (key, message) {
    const msgclass = (message.authorId === serverData.user.id) ? 'chatboxmessage my-message' : 'chatboxmessage'

    if (key >= 0 && conv.chatboxes[key] !== undefined) {
      conv.chatboxes[key].last_mid = parseInt(message.id)
      conv.chatboxes[key].el.children('.slimScrollDiv').children('.chatboxcontent').append(`
        <div title="${profileStore.profiles[message.authorId].name}" class="${msgclass}">
        <span class="chatboxmessagefrom">
          <a class="photo" href="${url('profile', message.authorId)}">
            <img src="${img(profileStore.profiles[message.authorId].avatar, 'mini')}">
          </a>
        </span>
        <span class="chatboxmessagecontent">
          ${plainToHtml(message.body)}
          <span class="time" title="${message.sentAt}">
            ${dateFormat(message.sentAt)}
          </span>
        </span>
        <div class="clear"></div>
      </div>
      `)
    }
  },

  /**
   * load the first content for one chatbox
   */
  initChat: async function (cid) {
    conv.showLoader(cid)

    const key = this.getKey(cid)

    try {
      await conversationStore.loadConversation(cid)
      const conversation = conversationStore.conversations[cid]
      /* disable leaving chats as it currently leads to undefined logical behaviour that breaks other behaviour :D
        conv.addChatOption(cid, `<a href="#" onclick="if(confirm('Bist Du Dir sicher, dass Du den Chat verlassen möchtest? Dadurch verlierst du unwiderruflich Zugriff auf alle Nachrichten in dieser Unterhaltung.')){conv.leaveConversation(${cid});}return false;">Chat verlassen</a>`)
      */
      conv.addChatOption(cid, `<span class="optinput"><input placeholder="Chat umbenennen..." type="text" name="chatname" value="" maxlength="30" /><i onclick="conv.rename(${cid}, $(this).prev().val())" class="fas fa-arrow-circle-right"></i></span>`)

      // first build a title from all the usernames
      let title = conversation.title
      if (title == null) {
        title = []
        for (const memberId of conversation.members) {
          if (memberId === serverData.user.id || profileStore.profiles[memberId].name === null) {
            continue
          }
          title.push(`
            <a href="${url('profile', memberId)}">
              ${plainToHtml(profileStore.profiles[memberId].name)}
            </a>
          `)
        }
        title = title.join(', ')
      } else if (conversation.storeId) {
        title = `
          <a href="${url('store', conversation.storeId)}">
            ${plainToHtml(title)}
          </a>
        `
      } else {
        title = plainToHtml(title)
      }

      if (key >= 0 && conv.chatboxes[key] !== undefined) {
        conv.chatboxes[key].el.children('.chatboxhead').children('.chatboxtitle').html(`
        <i class="fas fa-fw fa-comment fa-flip-horizontal"></i>
        ${title}
      `)
      }

      // now append all arrived messages
      Object.values(conversation.messages).forEach((m) => conv.append(key, m))
      conv.scrollBottom(cid)
    } catch (e) {
      pulseError('Fehler beim Laden der Unterhaltung')
      console.error(e)
    } finally {
      conv.hideLoader(cid)
      conv.storeOpenedChatWindows()
    }
  },

  rename: async function (cid, newName) {
    try {
      await api.renameConversation(cid, newName)
      const key = this.getKey(cid)
      conv.chatboxes[key].el.children('.chatboxhead').children('.chatboxtitle').html(`
        <i class="fas fa-fw fa-comment fa-flip-horizontal"></i>
        ${plainToHtml(newName)}
      `)
    } catch (e) {
      pulseError('Fehler beim Umbenennen der Unterhaltung')
      console.error(e)
    }
  },

  leaveConversation: async function (cid) {
    await api.removeUserFromConversation(cid, serverData.user.id)
    conv.close(cid)
    conversationStore.loadConversations()
  },

  appendChatbox: function (cid, min) {
    if (this.isBigPageMode) {
      return false
    }

    if (min == undefined) {
      min = false
    }
    if (conv.getKey(cid) === -1) {
      const right = 20 + (this.chatCount * 285)

      const $el = $(`
        <div id="chat-${cid}" class="chatbox ui-corner-top" style="bottom: 0px; right: ${right}px; display: block;"></div>
      `).appendTo('body')
      $el.html(`
        <div class="chatboxhead ui-corner-top">
          <div class="chatboxtitle" onclick="conv.togglebox(${cid});">
            <i class="fas fa-fw fa-spinner fa-spin"></i>
          </div>
          <ul style="display:none;" class="settings linklist linkbubble ui-shadow corner-all">
            <li>
              <a href="${url('conversations', cid)}">Alle Nachrichten</a>
            </li>
          </ul>
          <div class="chatboxoptions">
            <a href="#" title="Einstellungen" onclick="conv.settings(${cid});return false;">
              <i class="fas fa-fw fa-cog"></i>
            </a>
            <a title="schließen" href="#" onclick="conv.close(${cid});return false;">
              <i class="fas fa-fw fa-times"></i>
            </a>
          </div>
          <br clear="all"/>
        </div>
        <div class="chatboxcontent"></div>
        <div class="chatboxinput">
          <textarea placeholder="Schreibe etwas..." class="chatboxtextarea" onkeydown="conv.checkInputKey(event,this,'${cid}');"></textarea>
        </div>
      `)

      $el.children('.chatboxcontent').slimScroll()
      $el.children('.chatboxinput').children('textarea').autosize()

      $el.children('.chatboxinput').children('textarea').on('focus', function () {
        conversationStore.markAsRead(cid)
        conv.activeBox = cid
      })

      this.chatboxes.push({
        el: $el,
        id: cid,
        minimized: false,
        last_mid: 0,
      })

      this.chatCount++

      /*
       * do the init ajax call
       */
      this.initChat(cid)

      /*
       * focus textarea
       */
      $el.children('.chatboxinput').children('textarea').trigger('select')

      /*
       * register service new
       */
      if (min) {
        conv.minbox(cid)
      }
    } else {
      this.maxbox(cid)
    }
  },
  addChatOption: function (cid, el) {
    $(`#chat-${cid} .settings`).append(`<li>${el}</li>`)
  },
}
$(function () {
  if ($('body.loggedin').length > 0) {
    conv.init()
  }
})

export default conv
