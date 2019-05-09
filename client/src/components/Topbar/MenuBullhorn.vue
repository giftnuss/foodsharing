<template>
  <nav-item-dropdown
    :no-caret="!displayArrow"
    tooltip="Aktionen"
    right
  >
    <template slot="button-content">
      <i class="fas fa-bullhorn" />
      <span v-if="displayText">
        Aktionen
      </span>
    </template>
    <h3 class="dropdown-header">
      Politik
    </h3>
    <a
      :href="$url('fasten')"
      class="dropdown-item sub"
      role="menuitem"
    >
      Verschwendungsfasten
    </a>
    <a
      :href="$url('claims')"
      class="dropdown-item sub"
      role="menuitem"
    >
      Forderungen
    </a>
    <a
      :href="$url('leeretonne')"
      class="dropdown-item sub"
      role="menuitem"
    >
      Vergangene Kampagnen
    </a>
    <h3 class="dropdown-header">
      Bildung
    </h3>
    <a
      :href="$url('academy')"
      class="dropdown-item sub"
      role="menuitem"
    >
      Akademie
    </a>
    <a
      :href="$url('workshops')"
      class="dropdown-item sub"
      role="menuitem"
    >
      Vorträge und Workshops
    </a>
    <a
      :href="$url('festival')"
      class="dropdown-item sub"
      role="menuitem"
    >
      foodsharing Festival
    </a>
  </nav-item-dropdown>
</template>
<script>
import NavItemDropdown from './NavItemDropdown'

export default {
  components: { NavItemDropdown },
  props: {
    displayArrow: {
      type: Boolean,
      default: true
    },
    displayText: {
      type: Boolean,
      default: false
    }
  }
}
</script>
