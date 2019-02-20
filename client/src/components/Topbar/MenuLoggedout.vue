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

    <MenuBullhorn/>
    <MenuInformation :displayText="!isMobile"/>
    <MenuEnvelope/>
  </ul>
</template>

<script>
import NavItemDropdown from './NavItemDropdown'
import MenuBullhorn from './MenuBullhorn'
import MenuInformation from './MenuInformation'
import MenuEnvelope from './MenuEnvelope'

export default {
  components: { NavItemDropdown, MenuBullhorn, MenuInformation, MenuEnvelope },
  props: {
    isMobile: {
      type: Boolean,
      default: false
    }
  }
}
</script>
