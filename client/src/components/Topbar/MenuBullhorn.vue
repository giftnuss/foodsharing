<template>
  <b-nav-item-dropdown
    id="dropdown-bullhorn"
    v-b-tooltip="$i18n('menu.entry.activities')"
    :no-caret="!displayArrow"
    right
  >
    <template v-slot:button-content>
      <i class="fas fa-bullhorn" />
      <span v-if="displayText">
        {{ $i18n('menu.entry.activities') }}
      </span>
    </template>
    <h3 class="dropdown-header">
      {{ $i18n('menu.entry.politics') }}
    </h3>
    <a
      :href="$url('fsstaedte')"
      class="dropdown-item sub"
      role="menuitem"
    >
      {{ $i18n('menu.entry.fscities') }}
    </a>
    <a
      :href="$url('claims')"
      class="dropdown-item sub"
      role="menuitem"
    >
      {{ $i18n('menu.entry.demands') }}
    </a>
    <a
      :href="$url('leeretonne')"
      class="dropdown-item sub"
      role="menuitem"
    >
      {{ $i18n('menu.entry.pastcampaigns') }}
    </a>
    <h3 class="dropdown-header">
      {{ $i18n('menu.entry.education') }}
    </h3>
    <a
      :href="$url('academy')"
      class="dropdown-item sub"
      role="menuitem"
    >
      {{ $i18n('menu.entry.academy') }}
    </a>
    <a
      :href="$url('workshops')"
      class="dropdown-item sub"
      role="menuitem"
    >
      {{ $i18n('menu.entry.talksandworkshops') }}
    </a>
    <a
      :href="$url('festival')"
      class="dropdown-item sub"
      role="menuitem"
    >
      {{ $i18n('menu.entry.fsfestival') }}
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
