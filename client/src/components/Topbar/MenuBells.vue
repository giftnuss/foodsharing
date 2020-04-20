<template>
  <b-nav-item-dropdown
    id="dropdown-bells"
    v-b-tooltip="$i18n('menu.entry.notifications')"
    no-caret
    right
    class="topbar-bells"
  >
    <template v-slot:button-content>
      <i class="fas fa-bell" />
      <span
        v-if="unread"
        class="badge badge-danger"
      >
        {{ unread }}
      </span>
    </template>
    <div class="list-group">
      <small
        v-if="!bells.length"
        class="list-group-item text-muted"
      >
        {{ $i18n('menubells.no_bells') }}
      </small>
      <menu-bells-entry
        v-for="bell in bells"
        :key="bell.id"
        :bell="bell"
        @remove="onBellDelete"
        @bellClick="onBellClick"
      />
    </div>
  </b-nav-item-dropdown>
</template>
<script>
import MenuBellsEntry from './MenuBellsEntry'
import bellStore from '@/stores/bells'
import i18n from '@/i18n'
import { pulseError } from '@/script'
import dateFnsParseISO from 'date-fns/parseISO'

export default {
  components: { MenuBellsEntry },
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
    async onBellClick (bell) {
      if (!bell.isRead) {
        try {
          await bellStore.markAsRead(bell)
        } catch (err) {
          pulseError(i18n('error_unexpected'))
        }
      }

      window.location.href = bell.href
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
