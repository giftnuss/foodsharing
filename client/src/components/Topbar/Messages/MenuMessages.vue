<template>
  <fs-dropdown-menu
    id="dropdown-messages"
    menu-title="menu.entry.messages"
    icon="fa-comments"
    class="topbar-messages list-with-actions"
    :show-only-on-mobile="showOnlyOnMobile"
    :hide-only-on-mobile="hideOnlyOnMobile"
    right
  >
    <template #heading-text>
      <span
        v-if="unread"
        class="badge badge-danger"
      >
        {{ unread }}
      </span>
      <span v-else />
    </template>
    <div class="list-group menu-messages-width">
      <div class="scroll-container">
        <menu-messages-entry
          v-for="conversation in conversations"
          :key="conversation.id"
          :conversation="conversation"
        />
      </div>
    </div>
    <template #actions="{ hide }">
      <b-btn
        :disabled="!unread"
        :title="$i18n('menu.entry.mark_as_read')"
        secondary
        size="sm"
        @click="markUnreadMessagesAsRead(); hide();"
      >
        <i class="fas fa-fw fa-check-double" />
      </b-btn>
      <b-btn
        :href="$url('conversations')"
        secondary
        size="sm"
      >
        <i class="fas fa-comments" /> {{ $i18n('menu.entry.all_messages') }}
      </b-btn>
    </template>
  </fs-dropdown-menu>
</template>
<script>
import MenuMessagesEntry from './MenuMessagesEntry'
import conversationStore from '@/stores/conversations'
import FsDropdownMenu from '../FsDropdownMenu'

export default {
  components: { MenuMessagesEntry, FsDropdownMenu },
  props: {
    showOnlyOnMobile: { type: Boolean, default: false },
    hideOnlyOnMobile: { type: Boolean, default: false },
  },
  computed: {
    conversations () {
      /* let res = Array.from(conversationStore.conversations) // .filter(c => c.lastMessage || c.messages)
      return res */
      return Object.values(conversationStore.conversations).filter((a) => (a.lastMessage != null)).sort(
        (a, b) => (a.hasUnreadMessages === b.hasUnreadMessages) ? ((a.lastMessage.sentAt < b.lastMessage.sentAt) ? 1 : -1) : (a.hasUnreadMessages ? -1 : 1),
      )
    },
    unread () {
      return conversationStore.unreadCount
    },
  },
  created () {
    return conversationStore.loadConversations()
  },
  methods: {
    markUnreadMessagesAsRead () {
      conversationStore.markUnreadMessagesAsRead()
    },
  },
}
</script>

<style lang="scss">
  .topbar-messages {
    .dropdown-menu {
      width: 300px;
    }
  }
  .menu-messages-width {
    min-width: 280px;
  }
</style>
