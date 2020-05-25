<template>
  <b-navbar-nav
    class="nav-row justify-content-md-center"
  >
    <menu-item
      v-if="!hasFsRole"
      :url="$url('upgradeToFs')"
      icon="fa-rocket"
      :title="$i18n('foodsaver.upgrade.to_fs')"
      :show-title-always="true"
    />

    <menu-region
      v-if="hasFsRole"
      :regions="regions"
    />
    <menu-groups
      v-if="hasFsRole"
      :working-groups="workingGroups"
    />
    <menu-stores
      v-if="hasFsRole"
      :may-add-store="mayAddStore"
    />
    <menu-baskets :show-label="!hasFsRole" />
    <menu-item
      :url="$url('map')"
      icon="fa-map-marker-alt"
      :title="$i18n('storelist.map')"
      :hide-on-mobile="true"
      :hide-title-always="true"
    />
    <menu-messages :show-only-on-mobile="true" />
    <menu-bells :show-only-on-mobile="true" />
    <menu-user
      :avatar="avatar"
      :user-id="userId"
      :show-only-on-mobile="true"
    />
    <menu-item
      v-if="hasFsRole"
      id="search"
      icon="fa-search"
      :hide-title-always="true"
      :show-only-on-mobile="true"
      @click="$emit('openSearch')"
    />
  </b-navbar-nav>
</template>

<script>
import MenuItem from './MenuItem'
import MenuRegion from './MenuRegion'
import MenuStores from './Stores/MenuStores'
import MenuGroups from './MenuGroups'
import MenuBaskets from './Baskets/MenuBaskets'
import MenuMessages from './Messages/MenuMessages'
import MenuBells from './Bells/MenuBells'
import MenuUser from './MenuUser'

export default {
  components: {
    MenuItem,
    MenuRegion,
    MenuStores,
    MenuGroups,
    MenuBaskets,
    MenuMessages,
    MenuBells,
    MenuUser
  },

  props: {
    hasFsRole: {
      type: Boolean,
      default: true
    },
    regions: {
      type: Array,
      default: () => []
    },
    workingGroups: {
      type: Array,
      default: () => []
    },
    mayAddStore: {
      type: Boolean,
      default: false
    },
    avatar: {
      type: String,
      default: ''
    },
    userId: {
      type: Number,
      default: null
    }
  }
}

</script>

<style lang="scss" scoped>
.bootstrap .navbar-nav /deep/ .dropdown-menu {
  position: absolute;
}
</style>
