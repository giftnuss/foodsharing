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
        <i class="fas fa-cog" /> {{ $i18n('menu.your_settings') }}
      </a>
      <a
        :href="$url('logout')"
        role="menuitem"
        class="nav-link"
      >
        <i class="fas fa-power-off" /> {{ $i18n('login.logout') }}
      </a>
    </div>
    <b-nav-item-dropdown
      v-else
      v-b-tooltip="$i18n('menu.entry.your_account')"
      no-caret
      class="user"
      right
    >
      <template v-slot:button-content>
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
        <i class="fas fa-cog" /> {{ $i18n('menu.your_settings') }}
      </a>
      <a
        :href="$url('logout')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-power-off" /> {{ $i18n('login.logout') }}
      </a>
    </b-nav-item-dropdown>
  </div>
</template>
<script>

export default {
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
