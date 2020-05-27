<template>
  <fs-dropdown-menu
    menu-title="menu.entry.activities"
    :items="headings"
    icon="fa-bullhorn"
    right
  />
</template>

<script>
import FsDropdownMenu from './FsDropdownMenu'

export default {
  components: {
    FsDropdownMenu
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
