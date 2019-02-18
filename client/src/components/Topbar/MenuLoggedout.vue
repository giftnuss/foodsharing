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

    <nav-item-dropdown rightt>
      <template slot="button-content">
        <i class="fas fa-bullhorn " />
        <span v-if="!wXS">
          Über uns
        </span>
      </template>
      <h3 class="dropdown-header">
        Über uns
      </h3>

      <a
        :href="$url('vision')"
        class="dropdown-item sub"
        role="menuitem"
      >
        Vision
      </a>
      <a
        :href="$url('grundsaetze')"
        class="dropdown-item sub"
        role="menuitem"
      >
        Grundsätze
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
      <h3 class="dropdown-header">
        Bildung
      </h3>
      <a
        :href="$url('fs-academy')"
        class="dropdown-item sub"
        role="menuitem"
      >
        Akademie
      </a>
      <a
        :href="$url('fs-festival')"
        class="dropdown-item sub"
        role="menuitem"
      >
        Festival
      </a>
      <a
        :href="$url('infosCompany')"
        class="dropdown-item"
        role="menuitem"
      >
        Für Unternehmen
      </a>
    </nav-item-dropdown>

    <nav-item-dropdown right>
      <template slot="button-content">
        <i class="fas fa-info " />
        <span class="d-none d-lg-inline">
          Infos
        </span>
      </template>
      <a
        :href="$url('infos')"
        class="dropdown-item"
        role="menuitem"
      >
        Infosammlung
      </a>
      <a
        :href="$url('donate')"
        class="dropdown-item"
        role="menuitem"
      >
        Spenden
      </a>
      <a
        :href="$url('blog')"
        class="dropdown-item"
        role="menuitem"
      >
        Blog
      </a>
      <a
        :href="$url('faq')"
        class="dropdown-item"
        role="menuitem"
      >
        F.A.Q.
      </a>
      <a
        :href="$url('guide')"
        class="dropdown-item"
        role="menuitem"
      >
        Ratgeber
      </a>
      <a
        :href="$url('wiki')"
        class="dropdown-item"
        role="menuitem"
      >
        Wiki
      </a>
    </nav-item-dropdown>

    <nav-item-dropdown
      tooltip="Kontakt"
      right
    >
      <template slot="button-content">
        <i class="fas fa-envelope" />
      </template>
      <h3 class="dropdown-header">
        Communities
      </h3>
      <a
        :href="$url('communitiesGermany')"
        class="dropdown-item sub"
        role="menuitem"
      >
        Deutschland
      </a>
      <a
        :href="$url('communitiesAustria')"
        class="dropdown-item sub"
        role="menuitem"
      >
        Österreich
      </a>
      <a
        :href="$url('communitiesSwitzerland')"
        class="dropdown-item sub"
        role="menuitem"
      >
        Schweiz
      </a>
      <div class="dropdown-divider" />
      <a
        :href="$url('team')"
        class="dropdown-item"
        role="menuitem"
      >
        Team
      </a>
      <a
        :href="$url('press')"
        class="dropdown-item"
        role="menuitem"
      >
        Presse
      </a>
      <a
        :href="$url('imprint')"
        class="dropdown-item"
        role="menuitem"
      >
        Impressum
      </a>
    </nav-item-dropdown>
  </ul>
</template>

<script>
import NavItemDropdown from './NavItemDropdown'

export default {
  components: { NavItemDropdown },
  props: {
    wXS: {
      type: Boolean,
      default: true
    }
  }
}
</script>
