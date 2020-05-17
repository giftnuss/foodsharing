<template>
  <div>
    <menu-item
      :url="$url('profile', userId)"
      icon="fa-address-card"
      :title="$i18n('profile.title')"
      class="d-md-none"
    />
    <menu-item
      :url="$url('settings')"
      icon="fa-cog"
      :title="$i18n('settings.header')"
      class="d-md-none"
    />
    <menu-item
      :url="$url('logout')"
      icon="fa-power-off"
      :title="$i18n('login.logout') "
      class="d-md-none"
    />

    <b-nav-item-dropdown
      v-b-tooltip="$i18n('menu.entry.your_account')"
      no-caret
      class="user d-none d-md-inline-block"
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
        <i class="fas fa-cog" /> {{ $i18n('settings.header') }}
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
import MenuItem from './MenuItem'

export default {
  components: { MenuItem },
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
