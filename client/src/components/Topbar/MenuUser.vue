<template>
  <div>
    <fs-dropdown-menu
      v-b-tooltip="$i18n('menu.entry.your_account')"
      no-caret
      class="user"
      right
      menu-title="menu.entry.your_account"
      :show-only-on-mobile="showOnlyOnMobile"
      :hide-only-on-mobile="hideOnlyOnMobile"
    >
      <template #heading-text>
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
        href="#"
        role="menuitem"
        class="dropdown-item"
        @click="$refs.languageChooser.show()"
      >
        <i class="fas fa-language" /> {{ $i18n('menu.entry.language') }}
      </a>
      <a
        :href="$url('logout')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-power-off" /> {{ $i18n('login.logout') }}
      </a>
    </fs-dropdown-menu>

    <language-chooser
      ref="languageChooser"
    />
  </div>
</template>
<script>
import FsDropdownMenu from './FsDropdownMenu'
import LanguageChooser from './LanguageChooser'

export default {
  components: { LanguageChooser, FsDropdownMenu },
  props: {
    userId: {
      type: Number,
      default: null,
    },
    avatar: {
      type: String,
      default: null,
    },
    showOnlyOnMobile: { type: Boolean, default: false },
    hideOnlyOnMobile: { type: Boolean, default: false },
  },
}
</script>

<style lang="scss" scoped>
img {
    height: 1.2rem;
    border-radius: 16px;
    border: 1px solid var(--fs-brown);
    margin-bottom: 1px;
    margin-top: -2px;
}
</style>
