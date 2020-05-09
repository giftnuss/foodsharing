<template>
  <b-navbar-nav
    id="topbar-navright"
    class="ml-auto"
  >
    <menu-item
      :url="$url('map')"
      icon="fa-map-marker-alt"
      :title="$i18n('storelist.map')"
    />

    <MenuBullhorn />
    <MenuInformation />
    <MenuEnvelope />
  </b-navbar-nav>
</template>

<script>

import { VBTooltip, BNavbarNav } from 'bootstrap-vue'
import MenuBullhorn from './MenuBullhorn'
import MenuInformation from './MenuInformation'
import MenuEnvelope from './MenuEnvelope'
import MenuItem from './MenuItem'

export default {
  components: { MenuBullhorn, MenuInformation, MenuEnvelope, BNavbarNav, MenuItem },
  directives: { VBTooltip },
  props: {
    isMobile: {
      type: Boolean,
      default: false
    }
  }
}
</script>
