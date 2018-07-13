<template>
    <nav-item-dropdown tooltip="Nachrichten" no-caret right class="topbar-messages">
        <template slot="button-content">
            <i class="fa fa-comments"/>
            <span class="badge badge-danger">{{ unread }}</span>
        </template>
        <div class="list-group">
            <menu-messages-entry
                v-for="conversation in conversations"
                :key="conversation.id"
                :conversation="conversation"
            />
        </div>
        <a :href="$url('conversations')" class="dropdown-item bg-secondary text-white text-center">
            Alle Nachrichten
        </a>
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
    created() {
        return conversationStore.loadConversations()
    },

    computed: {
        conversations() {
            return conversationStore.conversations.slice(0, 10)
        },

        unread() {
            let unreadMessages = 0
            for(let conv of this.conversations) {
                if(conv.hasUnreadMessages) unreadMessages++
            }
            return unreadMessages
        }
    }
}
</script>

<style lang="scss">
.topbar-messages {
    .list-group {
        width: 250px;
    }
    .dropdown-menu {
        padding: 0;
    }
}
</style>
 