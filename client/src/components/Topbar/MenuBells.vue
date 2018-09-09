<template>
  <nav-item-dropdown
    tooltip="Benachrichtigungen"
    no-caret
    right
    class="topbar-bells">
    <template slot="button-content">
      <i class="fas fa-bell"/>
      <span
        v-if="unread"
        class="badge badge-danger">{{ unread }}</span>
    </template>
    <div class="list-group">
      <small
        v-if="!bells.length"
        class="list-group-item text-muted">Du hast derzeit keine Benachrichtigungen</small>
      <menu-bells-entry
              v-for="bell in bells"
              :key="bell.id"
              :bell="bell"
              @remove="onBellDelete"
              @bellClick="onBellClick"
      />
    </div>
  </nav-item-dropdown>
</template>
<script>
import NavItemDropdown from './NavItemDropdown'
import MenuBellsEntry from './MenuBellsEntry'
import bellStore from '@/stores/bells'
import i18n from '@/i18n'
import { pulseError } from '@/script'

export default {
  components: {
    NavItemDropdown,
    MenuBellsEntry
  },
  computed: {
    bells () {
      return bellStore.bells
    },
    unread () {
      return bellStore.unreadCount
    }
  },
  created () {
    bellStore.loadBells()
  },
  methods: {
    async onBellDelete (id) {
      try {
        await bellStore.delete(id)
      } catch (err) {
        pulseError(i18n('error_unexpected'))
      }
    },
      async onBellClick(id) {
          try {
              await bellStore.markAsRead(id)
          } catch (err) {
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
