<template>
    <nav-item-dropdown tooltip="Benachrichtigungen" no-caret right class="topbar-bells">
        <template slot="button-content">
            <i class="fa fa-bell"/>
            <span v-if="unread" class="badge badge-danger">{{ unread }}</span>
        </template>
        <div class="list-group">
            <small v-if="!bells.length" class="list-group-item text-muted">Du hast derzeit keine Benachrichtungen</small>
            <menu-bells-entry
                v-for="bell in bells"
                :key="bell.id"
                :bell="bell"
                @remove="onBellDelete"
            />
        </div>
    </nav-item-dropdown>
</template>
<script>
import NavItemDropdown from './NavItemDropdown'
import MenuBellsEntry from './MenuBellsEntry'
import bellStore from '@/stores/bells'
import i18n from '@/i18n'

export default {
    components: {
        NavItemDropdown,
        MenuBellsEntry
    },
    created() {
        bellStore.loadBells()
    },
    computed: {
        bells() {
            return bellStore.bells
        },
        unread() {
            return bellStore.unreadCount
        }
    },
    methods: {
        async onBellDelete(id) {
            try {
                await bellStore.delete(id)
            } catch(err) {
                pulseError(i18n('error_unexpected'))
            }
        }
    }
}
</script>
<style lang="scss">
.topbar-bells {
    .dropdown-menu {
        width: 250px;
        padding: 0;
    }
}
</style>
