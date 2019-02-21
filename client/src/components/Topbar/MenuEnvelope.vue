<template>
  <nav-item-dropdown
    :no-caret="!displayArrow"
    right
    tooltip="Kontakt"
  >
    <template slot="button-content">
      <i class="fas fa-envelope" />
      <span v-if="displayText">
        Kontakt
      </span>
    </template>
    <a
      v-if="displayMailbox"
      :href="$url('mailbox')"
      class="dropdown-item"
      role="menuitem"
    >
      E-Mail-Postfach
    </a>
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
    },
    displayMailbox: {
      type: Boolean,
      default: false
    }
  }
}
</script>
