<template>
  <nav-item-dropdown
    ref="dropdown"
    tooltip="Nachrichten"
    no-caret
    right
    class="topbar-messages">
    <template slot="button-content">
      <i class="fas fa-comments"/>
      <span
        v-if="unread"
        class="badge badge-danger">{{ unread }}</span>
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
        class="btn btn-sm btn-secondary">
        <i class="fas fa-comments" /> Alle Nachrichten
      </a>
    </div>
  </nav-item-dropdown>
</template>
<script>
import NavItemDropdown from './NavItemDropdown'
import MenuMessagesEntry from './MenuMessagesEntry'
import conversationStore from '@/stores/conversations'

export default {
  components: {
    NavItemDropdown,
    MenuMessagesEntry
  },

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
