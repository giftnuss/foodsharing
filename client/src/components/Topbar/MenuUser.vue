<template>
  <div>
    <div
      v-if="isMobile"
      class="nav-item b-nav-dropdown dropdown"
    >
      <a
        :href="$url('profile', userId)"
        role="menuitem"
        class="nav-link"
      >
        <i class="fas fa-address-card" /> {{ $i18n('profile.title') }}
      </a>
      <a
        :href="$url('settings')"
        role="menuitem"
        class="nav-link"
      >
        <i class="fas fa-cog" /> {{ $i18n('settings') }}
      </a>
      <a
        :href="$url('logout')"
        role="menuitem"
        class="nav-link"
      >
        <i class="fas fa-power-off" /> {{ $i18n('login.logout') }}
      </a>
    </div>
    <nav-item-dropdown
      :tooltip="$i18n('menu.tooltips.your_account')"
      v-else
      no-caret
      class="user"
      right
    >
      <template slot="button-content">
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
        <i class="fas fa-cog" /> {{ $i18n('settings') }}
      </a>
      <a
        :href="$url('logout')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-power-off" /> {{ $i18n('login.logout') }}
      </a>
    </nav-item-dropdown>
  </div>
</template>
<script>

import NavItemDropdown from './NavItemDropdown'

export default {
  components: {
    NavItemDropdown
  },
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
}
</style>
