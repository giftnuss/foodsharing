<template>
  <div>
    <fs-dropdown-menu
      v-b-tooltip="$i18n('menu.entry.your_account')"
      no-caret
      class="user"
      right
      menu-title="menu.entry.your_account"
    >
      <template v-slot:heading-text>
        <img :src="avatar">
      </template>
      <a
        :href="$url('profile', userId)"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-address-card" /> {{ $i18n('profile.title') }}
      </a>
      <a
        :href="$url('settings')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-cog" /> {{ $i18n('settings.header') }}
      </a>
      <a
        :href="$url('logout')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-power-off" /> {{ $i18n('login.logout') }}
      </a>
    </fs-dropdown-menu>
  </div>
</template>
<script>
import FsDropdownMenu from './FsDropdownMenu'

export default {
  components: { FsDropdownMenu },
  props: {
    userId: {
      type: Number,
      default: null
    },
    avatar: {
      type: String,
      default: null
    },
    isMobile: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style lang="scss" scoped>
img {
    height: 1.2rem;
    border-radius: 16px;
    border: 1px solid white;
    margin-bottom: 1px;
    margin-top: -2px;
}
</style>
