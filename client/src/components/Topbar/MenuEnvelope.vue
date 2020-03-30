<template>
  <b-nav-item-dropdown
    id="dropdown-envelope"
    v-b-tooltip="$i18n('menu.tooltips.contact')"
    :no-caret="!displayArrow"
    right
  >
    <template v-slot:button-content>
      <i class="fas fa-envelope" />
      <span class="sr-only">{{ $i18n('menu.tooltips.contact') }}</span>
      <span v-if="displayText">{{ $i18n('menu.tooltips.contact') }}</span>
    </template>
    <b-dropdown-item
      v-if="displayMailbox"
      :href="$url('mailbox')"
    >
      E-Mail-Postfach
    </b-dropdown-item>
    <b-dropdown-divider
      v-if="displayMailbox"
    />
    <b-dropdown-item
      :href="$url('contact')"
    >
      {{ $i18n('menu.tooltips.contact') }}
    </b-dropdown-item>
    <b-dropdown-item
      :href="$url('donate')"
    >
      Spenden
    </b-dropdown-item>
    <b-dropdown-item
      :href="$url('press')"
    >
      Presse
    </b-dropdown-item>
    <b-dropdown-item
      :href="$url('infosCompany')"
    >
      Für Unternehmen
    </b-dropdown-item>
    <b-dropdown-item
      :href="$url('imprint')"
    >
      Impressum
    </b-dropdown-item>
    <b-dropdown-divider />
    <b-dropdown-header
      id="dropdown-header-groups"
    >
      Ortsgruppen
    </b-dropdown-header>
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
  </b-nav-item-dropdown>
</template>
<script>

import { BDropdownDivider, BDropdownItem, BNavItemDropdown } from 'bootstrap-vue'

export default {
  components: { BDropdownDivider, BDropdownItem, BNavItemDropdown },
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
