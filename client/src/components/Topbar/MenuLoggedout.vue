<template>
  <b-navbar-nav
    id="topbar-navright"
    class="ml-auto"
  >
    <li class="nav-item">
      <a
        v-b-tooltip.hover.bottom
        :href="$url('map')"
        :title="$i18n('storelist.map')"
        :aria-label="$i18n('storelist.map')"
        class="nav-link"
      >
        <i class="fas fa-map-marker-alt" />
        <span class="d-sm-none">
          {{ $i18n('storelist.map') }}
        </span>
      </a>
    </li>

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

export default {
  components: { MenuBullhorn, MenuInformation, MenuEnvelope, BNavbarNav },
  directives: { VBTooltip },
  props: {
    isMobile: {
      type: Boolean,
      default: false
    }
  }
}
</script>
