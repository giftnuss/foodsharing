<template>
  <ul
    id="topbar-navright"
    class="navbar-nav ml-auto no-collapse"
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
      </a>
    </li>
    <li class="nav-item">
      <a
        :href="$url('joininfo')"
        class="nav-link"
      >
        <i class="fas fa-rocket" />
        {{ $i18n('register.topbar') }}
      </a>
    </li>

    <MenuBullhorn />
    <MenuInformation />
    <MenuEnvelope />
  </ul>
</template>

<script>

import { VBTooltip } from 'bootstrap-vue'
import MenuBullhorn from './MenuBullhorn'
import MenuInformation from './MenuInformation'
import MenuEnvelope from './MenuEnvelope'

export default {
  components: { MenuBullhorn, MenuInformation, MenuEnvelope },
  directives: { VBTooltip },
  props: {
    isMobile: {
      type: Boolean,
      default: false
    }
  }
}
</script>
