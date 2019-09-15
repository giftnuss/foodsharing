<template>
  <nav-item-dropdown
    :no-caret="!displayArrow"
    tooltip="Infos"
    right
  >
    <template slot="button-content">
      <i class="fas fa-info " />
      <span v-if="displayText">
        Infos
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
      :href="$url('blog')"
      class="dropdown-item sub"
      role="menuitem"
    >
      Blog
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
    <h3 class="dropdown-header">
      Hintergrund
    </h3>
    <a
      :href="$url('faq')"
      class="dropdown-item sub"
      role="menuitem"
    >
      F.A.Q.
    </a>
    <a
      :href="$url('wiki')"
      class="dropdown-item sub"
      target="_blank"
      rel="noopener noreferrer nofollow"
      role="menuitem"
    >
      Wiki
    </a>
    <a
      :href="$url('guide')"
      class="dropdown-item sub"
      role="menuitem"
    >
      Ratgeber
    </a>
    <a
      :href="$url('statistics')"
      class="dropdown-item sub"
      role="menuitem"
    >
      Statistik
    </a>
    <a
      :href="$url('transparency')"
      class="dropdown-item sub"
      role="menuitem"
    >
      Transparenz
    </a>
    <a
      :href="$url('dataprivacy')"
      class="dropdown-item sub"
      role="menuitem"
    >
      Datenschutz
    </a>
    <a
      :href="$url('changelog')"
      class="dropdown-item sub"
      role="menuitem"
    >
      Changelog
    </a>
  </nav-item-dropdown>
</template>
<script>
import NavItemDropdown from './NavItemDropdown'

export default {
  components: { NavItemDropdown },
  props: {
    wXS: {
      type: Boolean,
      default: true
    },
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
