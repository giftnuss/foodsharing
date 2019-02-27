<template>
  <ul
    id="topbar-navright"
    class="navbar-nav ml-auto no-collapse"
  >
    <li class="nav-item">
      <a
        :href="$url('map')"
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
        Mach mit!
      </a>
    </li>

    <MenuBullhorn />
    <MenuInformation :display-text="!isMobile" />
    <MenuEnvelope />
  </ul>
</template>

<script>
import MenuBullhorn from './MenuBullhorn'
import MenuInformation from './MenuInformation'
import MenuEnvelope from './MenuEnvelope'

export default {
  components: { MenuBullhorn, MenuInformation, MenuEnvelope },
  props: {
    isMobile: {
      type: Boolean,
      default: false
    }
  }
}
</script>
