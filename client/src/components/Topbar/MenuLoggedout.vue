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

    <nav-item-dropdown right>
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
        :href="$url('festival')"
        class="dropdown-item sub"
        role="menuitem"
      >
        foodsharing Festival
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
        :href="$url('faq')"
        class="dropdown-item"
        role="menuitem"
      >
        F.A.Q.
      </a>        
      <a
        :href="$url('blog')"
        class="dropdown-item"
        role="menuitem"
      >
        Blog
      </a>
      <a
        :href="$url('wiki')"
        class="dropdown-item"
        role="menuitem"
      >
        Wiki
      </a>
      <a
        :href="$url('guide')"
        class="dropdown-item"
        role="menuitem"
      >
        Ratgeber
      </a>
      <a
        :href="$url('transparency')"
        class="dropdown-item"
        role="menuitem"
      >
        Transparenz
      </a>
      <a
        :href="$url('dataprivacy')"
        class="dropdown-item"
        role="menuitem"
      >
        Datenschutz
      </a>
      <a
        :href="$url('changelog')"
        class="dropdown-item"
        role="menuitem"
      >
        Changelog
      </a>        
    </nav-item-dropdown>

    <nav-item-dropdown
      tooltip="Kontakt"
      right
    >
      <template slot="button-content">
        <i class="fas fa-envelope" />
      </template>
       <a
        :href="$url('contact')"
        class="dropdown-item"
        role="menuitem"
      >
        Kontakt
      </a>        
      <a
        :href="$url('donate')"
        class="dropdown-item"
        role="menuitem"
      >
        Spenden
      </a>
      <a
        :href="$url('press')"
        class="dropdown-item"
        role="menuitem"
      >
        Presse
      </a>        
      <a
        :href="$url('infosCompany')"
        class="dropdown-item"
        role="menuitem"
      >
        Für Unternehmen
      </a>
      <a
        :href="$url('imprint')"
        class="dropdown-item"
        role="menuitem"
      >
        Impressum
      </a>
      <div class="dropdown-divider" />
      <h3 class="dropdown-header">
        Ortsgruppen
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
      <a
        :href="$url('international')"
        class="dropdown-item sub"
        role="menuitem"
      >
        International
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
