<template>
  <b-nav-item-dropdown
    id="dropdown-messages"
    ref="dropdown"
    v-b-tooltip="$i18n('menu.entry.messages')"
    no-caret
    right
    class="topbar-messages"
  >
    <template v-slot:button-content>
      <i class="fas fa-comments" />
      <span
        v-if="unread"
        class="badge badge-danger"
      >
        {{ unread }}
      </span>
    </template>
    <div class="list-group">
      <div class="scroll-container">
        <menu-messages-entry
          v-for="conversation in conversations"
          :key="conversation.id"
          :conversation="conversation"
          @chatOpened="close"
        />
      </div>
    </div>
    <div class="btn-group special btn-group-sm">
      <a
        v-if="unread"
        class="btn btn-sm btn-secondary"
        @click="markUnreadMessagesAsRead"
      >
        <i class="fas fa-check" /> {{ $i18n('menu.entry.mark_as_read') }}
      </a>
      <a
        :href="$url('conversations')"
        class="btn btn-sm btn-secondary"
      >
        <i class="fas fa-comments" /> {{ $i18n('menu.entry.all_messages') }}
      </a>
    </div>
  </b-nav-item-dropdown>
</template>
<script>
import MenuMessagesEntry from './MenuMessagesEntry'
import conversationStore from '@/stores/conversations'

export default {
  components: { MenuMessagesEntry },

  computed: {
    conversations () {
      /* let res = Array.from(conversationStore.conversations) // .filter(c => c.lastMessage || c.messages)
      return res */
      return Object.values(conversationStore.conversations).filter((a) => (a.lastMessage != null)).sort(
        (a, b) => (a.hasUnreadMessages === b.hasUnreadMessages) ? ((a.lastMessage.sentAt < b.lastMessage.sentAt) ? 1 : -1) : (a.hasUnreadMessages ? -1 : 1)
      )
    },
    unread () {
      return conversationStore.unreadCount
    }
  },
  created () {
    return conversationStore.loadConversations()
  },
  methods: {
    close () {
      this.$refs.dropdown.visible = false
    },
    markUnreadMessagesAsRead () {
      conversationStore.markUnreadMessagesAsRead()
      this.close()
    }
  }
}
</script>

<style lang="scss">
  .topbar-messages {
    .dropdown-menu {
      padding: 0;
    }
  }

  .btn-group.special {
    display: flex;
  }

  .special .btn {
    flex: 1
  }
</style>
