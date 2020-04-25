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
    <div class="list-grou-item p-2 text-right">
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
      return conversationStore.conversations
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
</style>
