<template>
  <b-nav-item-dropdown
    id="dropdown-bullhorn"
    v-b-tooltip="$i18n('menu.tooltips.activities')"
    :no-caret="!displayArrow"
    right
  >
    <template v-slot:button-content>
      <i class="fas fa-bullhorn" />
      <span v-if="displayText">
        {{ $i18n('menu.tooltips.activities') }}
      </span>
    </template>
    <h3 class="dropdown-header">
      Politik
    </h3>
    <a
      :href="$url('fsstaedte')"
      class="dropdown-item sub"
      role="menuitem"
    >
      foodsharing-Städte
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
  </b-nav-item-dropdown>
</template>
<script>

export default {
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
