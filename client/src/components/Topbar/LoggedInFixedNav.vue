<template>
  <b-navbar-nav
    class="nav-row justify-content-md-center"
  >
    <menu-item
      v-if="!hasFsRole"
      :url="$url('upgradeToFs')"
      icon="fa-rocket"
      :title="$i18n('foodsaver.upgrade_to')"
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
      v-if="hasFsRole && stores.length"
      :stores="stores"
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
    <menu-messages class="d-md-none" />
    <menu-bells class="d-md-none" />
    <menu-item
      v-if="hasFsRole"
      id="search"
      icon="fa-search"
      :hide-title-always="true"
      class="d-sm-none"
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

export default {
  components: {
    MenuItem,
    MenuRegion,
    MenuStores,
    MenuGroups,
    MenuBaskets,
    MenuMessages,
    MenuBells
  },

  props: {
    hasFsRole: {
      type: Boolean,
      default: true
    },
    stores: {
      type: Array,
      default: () => []
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
    }
  }
}

</script>

<style lang="scss" scoped>
.bootstrap .navbar-nav /deep/ .dropdown-menu {
  position: absolute;
}
@media (max-width: 500px) {
  .dropdown {
      position: initial;
    /deep/ .dropdown-menu {
      width: 100%;
      max-width: initial;
      top: 2.2em;
    }
  }

  .dropdown-menu .scroll-container {
      width: 100%;
  }
}
</style>
