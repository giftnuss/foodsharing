<template>
  <b-nav-item-dropdown
    id="dropdown-information"
    v-b-tooltip="$i18n('menu.entry.infos')"
    :no-caret="!displayArrow"
    right
  >
    <template v-slot:button-content>
      <i class="fas fa-info " />
      <span v-if="displayText">
        {{ $i18n('menu.entry.infos') }}
      </span>
    </template>
    <h3 class="dropdown-header">
      {{ $i18n('menu.entry.aboutUs') }}
    </h3>
    <a
      :href="$url('mission')"
      class="dropdown-item sub"
      role="menuitem"
    >
      {{ $i18n('menu.entry.mission') }}
    </a>
    <a
      :href="$url('grundsaetze')"
      class="dropdown-item sub"
      role="menuitem"
    >
      {{ $i18n('menu.entry.fundamentals') }}
    </a>
    <a
      :href="$url('blog')"
      class="dropdown-item sub"
      role="menuitem"
    >
      {{ $i18n('menu.entry.blog') }}
    </a>
    <a
      :href="$url('team')"
      class="dropdown-item sub"
      role="menuitem"
    >
      {{ $i18n('menu.entry.team') }}
    </a>
    <a
      :href="$url('partner')"
      class="dropdown-item sub"
      role="menuitem"
    >
      {{ $i18n('menu.entry.partners') }}
    </a>
    <h3 class="dropdown-header">
      {{ $i18n('menu.entry.background') }}
    </h3>
    <a
      :href="$url('faq')"
      class="dropdown-item sub"
      role="menuitem"
    >
      {{ $i18n('menu.entry.faq') }}
    </a>
    <a
      :href="$url('wiki')"
      class="dropdown-item sub"
      target="_blank"
      rel="noopener noreferrer nofollow"
      role="menuitem"
    >
      {{ $i18n('menu.entry.wiki') }}
    </a>
    <a
      :href="$url('guide')"
      class="dropdown-item sub"
      role="menuitem"
    >
      {{ $i18n('menu.entry.guide') }}
    </a>
    <a
      :href="$url('statistics')"
      class="dropdown-item sub"
      role="menuitem"
    >
      {{ $i18n('menu.entry.statistics') }}
    </a>
    <a
      :href="$url('transparency')"
      class="dropdown-item sub"
      role="menuitem"
    >
      {{ $i18n('menu.entry.transparency') }}
    </a>
    <a
      :href="$url('dataprivacy')"
      class="dropdown-item sub"
      role="menuitem"
    >
      {{ $i18n('menu.entry.dataprivacy') }}
    </a>
    <a
      :href="$url('changelog')"
      class="dropdown-item sub"
      role="menuitem"
    >
      {{ $i18n('menu.entry.changelog') }}
    </a>
  </b-nav-item-dropdown>
</template>
<script>

export default {
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
