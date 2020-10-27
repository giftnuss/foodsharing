<template>
  <fs-dropdown-menu
    id="dropdown-bells"
    menu-title="menu.entry.notifications"
    icon="fa-bell"
    right
    class="topbar-bells list-with-actions"
    :show-only-on-mobile="showOnlyOnMobile"
    :hide-only-on-mobile="hideOnlyOnMobile"
  >
    <template v-slot:heading-text>
      <span
        v-if="unread"
        class="badge badge-danger"
      >
        {{ unread }}
      </span>
      <span v-else />
    </template>
    <div class="list-group">
      <small
        v-if="!bells.length"
        class="list-group-item text-muted"
      >
        {{ $i18n('bell.no_bells') }}
      </small>
      <div class="scroll-container">
        <menu-bells-entry
          v-for="bell in bells"
          :key="bell.id"
          :bell="bell"
          @remove="onBellDelete"
          @bellRead="onBellRead"
        />
      </div>
    </div>
    <template v-slot:actions>
      <b-btn
        secondary
        size="sm"
        :disabled="unread"
        @click="markNewBellsAsRead"
      >
        <i class="fas fa-check" /> {{ $i18n('menu.entry.mark_as_read') }}
      </b-btn>
    </template>
  </fs-dropdown-menu>
</template>
<script>
import MenuBellsEntry from './MenuBellsEntry'
import bellStore from '@/stores/bells'
import i18n from '@/i18n'
import { pulseError } from '@/script'
import dateFnsParseISO from 'date-fns/parseISO'
import FsDropdownMenu from '../FsDropdownMenu'

export default {
  components: { MenuBellsEntry, FsDropdownMenu },
  props: {
    showOnlyOnMobile: { type: Boolean, default: false },
    hideOnlyOnMobile: { type: Boolean, default: false },
  },
  computed: {
    bells () {
      return bellStore.bells.map(bell => {
        var newBell = Object.assign({}, bell)
        newBell.createdAt = dateFnsParseISO(bell.createdAt)
        return newBell
      })
    },
    unread () {
      return bellStore.unreadCount
    },
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
    async onBellRead (bell) {
      if (!bell.isRead) {
        try {
          await bellStore.markAsRead(bell)
        } catch (err) {
          pulseError(i18n('error_unexpected'))
        }
      }
    },
    markNewBellsAsRead () {
      try {
        bellStore.markNewBellsAsRead()
      } catch {
        pulseError(i18n('error_unexpected'))
      }
    },
  },
}
</script>
<style lang="scss">
.topbar-bells {
    .dropdown-menu {
        min-width: 280px;
    }
}
</style>
