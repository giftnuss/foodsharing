<template>
  <b-nav-item-dropdown
    id="dropdown-bullhorn"
    v-b-tooltip="$i18n('menu.entry.activities')"
    :no-caret="!displayArrow"
    right
  >
    <template v-slot:button-content>
      <i class="fas fa-bullhorn" />
      <span class="d-md-none">
        {{ $i18n('menu.entry.activities') }}
      </span>
    </template>
    <template v-for="heading in headings">
      <h3
        :key="heading.heading"
        class="dropdown-header"
      >
        {{ $i18n(heading.heading) }}
      </h3>
      <a
        v-for="item in heading.menuItems"
        :key="item.url"
        :href="$url(item.url)"
        class="dropdown-item sub"
        role="menuitem"
      >
        {{ $i18n(item.menuTitle) }}
      </a>
    </template>
  </b-nav-item-dropdown>
</template>
<script>

export default {
  props: {
    displayArrow: {
      type: Boolean,
      default: true
    }
  },
  data () {
    return {
      headings: [
        {
          heading: 'menu.entry.politics',
          menuItems: [
            { url: 'fsstaedte', menuTitle: 'menu.entry.fscities' },
            { url: 'claims', menuTitle: 'menu.entry.demands' },
            { url: 'leeretonne', menuTitle: 'menu.entry.pastcampaigns' }
          ]
        },
        {
          heading: 'menu.entry.education',
          menuItems: [
            { url: 'academy', menuTitle: 'menu.entry.academy' },
            { url: 'workshops', menuTitle: 'menu.entry.talksandworkshops' },
            { url: 'festival', menuTitle: 'menu.entry.fsfestival' }
          ]
        }
      ]
    }
  }
}
</script>
