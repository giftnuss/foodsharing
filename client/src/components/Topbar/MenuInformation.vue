<template>
  <fs-dropdown-menu
    menu-title="menu.entry.infos"
    :items="headings"
    icon="fa-info"
    right
  />
</template>
<script>
import FsDropdownMenu from './FsDropdownMenu'

export default {
  components: { FsDropdownMenu },
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
  },
  data () {
    return {
      headings:
      [
        {
          heading: 'menu.entry.aboutUs',
          menuItems: [
            { url: 'mission', menuTitle: 'menu.entry.mission' },
            { url: 'grundsaetze', menuTitle: 'menu.entry.fundamentals' },
            { url: 'blog', menuTitle: 'menu.entry.blog' },
            { url: 'team', menuTitle: 'menu.entry.team' },
            { url: 'partner', menuTitle: 'menu.entry.partners' }
          ]
        },
        {
          heading: 'menu.entry.background',
          menuItems: [
            { url: 'support', menuTitle: 'menu.entry.support', target: '_blank' },
            { url: 'wiki', menuTitle: 'menu.entry.wiki' },
            { url: 'guide', menuTitle: 'menu.entry.guide', target: '_blank' },
            { url: 'statistics', menuTitle: 'menu.entry.statistics' },
            { url: 'transparency', menuTitle: 'menu.entry.transparency' },
            { url: 'dataprivacy', menuTitle: 'menu.entry.dataprivacy' },
            { url: 'releaseNotes', menuTitle: 'menu.entry.release-notes' }
          ]
        }
      ]
    }
  }
}
</script>
