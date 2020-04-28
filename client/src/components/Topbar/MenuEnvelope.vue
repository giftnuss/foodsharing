<template>
  <b-nav-item-dropdown
    id="dropdown-envelope"
    v-b-tooltip="$i18n('menu.entry.contact')"
    :no-caret="!displayArrow"
    right
  >
    <template v-slot:button-content>
      <i class="fas fa-envelope" />
      <span class="sr-only">{{ $i18n('menu.entry.contact') }}</span>
      <span v-if="displayText">{{ $i18n('menu.entry.contact') }}</span>
    </template>
    <b-dropdown-item
      v-if="displayMailbox"
      :href="$url('mailbox')"
    >
      {{ $i18n('menu.entry.mailbox') }}
    </b-dropdown-item>
    <b-dropdown-divider
      v-if="displayMailbox"
    />
    <b-dropdown-item
      :href="$url('contact')"
    >
      {{ $i18n('menu.entry.contact') }}
    </b-dropdown-item>
    <b-dropdown-item
      :href="$url('donate')"
    >
      {{ $i18n('menu.entry.donate') }}
    </b-dropdown-item>
    <b-dropdown-item
      :href="$url('press')"
    >
      {{ $i18n('menu.entry.press') }}
    </b-dropdown-item>
    <b-dropdown-item
      :href="$url('infosCompany')"
    >
      {{ $i18n('menu.entry.forcompanies') }}
    </b-dropdown-item>
    <b-dropdown-item
      :href="$url('imprint')"
    >
      {{ $i18n('menu.entry.imprint') }}
    </b-dropdown-item>
    <b-dropdown-divider />
    <b-dropdown-header
      id="dropdown-header-groups"
    >
      {{ $i18n('menu.entry.regionalgroups') }}
    </b-dropdown-header>
    <a
      :href="$url('communitiesGermany')"
      class="dropdown-item sub"
      role="menuitem"
    >
      {{ $i18n('menu.entry.Germany') }}
    </a>
    <a
      :href="$url('communitiesAustria')"
      class="dropdown-item sub"
      role="menuitem"
    >
      {{ $i18n('menu.entry.Austria') }}
    </a>
    <a
      :href="$url('communitiesSwitzerland')"
      class="dropdown-item sub"
      role="menuitem"
    >
      {{ $i18n('menu.entry.Swiss') }}
    </a>
    <a
      :href="$url('international')"
      class="dropdown-item sub"
      role="menuitem"
    >
      {{ $i18n('menu.entry.international') }}
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
