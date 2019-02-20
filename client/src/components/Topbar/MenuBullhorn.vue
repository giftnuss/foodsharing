<template>
  <nav-item-dropdown right :no-caret="!displayArrow">
      <template slot="button-content">
        <i class="fas fa-bullhorn" />
        <span v-if="displayText">
          Aktionen
        </span>
      </template>
      <h3 class="dropdown-header">
        Über uns
      </h3>

      <a
        :href="$url('mission')"
        class="dropdown-item sub"
        role="menuitem"
      >
        Mission
      </a>
      <a
        :href="$url('grundsaetze')"
        class="dropdown-item sub"
        role="menuitem"
      >
        Grundsätze
      </a>
      <a
        :href="$url('team')"
        class="dropdown-item sub"
        role="menuitem"
      >
        Team
      </a>
      <a
        :href="$url('partner')"
        class="dropdown-item sub"
        role="menuitem"
      >
        Partner
      </a>
      <a
        :href="$url('statistics')"
        class="dropdown-item sub"
        role="menuitem"
      >
        Statistik
      </a>
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