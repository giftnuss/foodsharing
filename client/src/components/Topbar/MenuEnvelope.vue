<template>
  <fs-dropdown-menu
    menu-title="menu.entry.contact"
    :items="headings"
    icon="fa-envelope"
  />
</template>

<script>
import FsDropdownMenu from './FsDropdownMenu'

export default {
  components: { FsDropdownMenu },
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
  },
  data () {
    const contactMenuItems = []
    if (this.displayMailbox) {
      contactMenuItems.push({ url: 'mailbox', menuTitle: 'menu.entry.mailbox' })
    }
    contactMenuItems.push({ url: 'mailbox', menuTitle: 'menu.entry.mailbox' })
    contactMenuItems.push({ url: 'contact', menuTitle: 'menu.entry.contact' })
    contactMenuItems.push({ url: 'donate', menuTitle: 'menu.entry.donate' })
    contactMenuItems.push({ url: 'press', menuTitle: 'menu.entry.press' })
    contactMenuItems.push({ url: 'infosCompany', menuTitle: 'menu.entry.forcompanies' })
    contactMenuItems.push({ url: 'imprint', menuTitle: 'menu.entry.imprint' })
    return {
      headings: [{
        heading: 'menu.entry.contact',
        menuItems: contactMenuItems
      },
      {
        heading: 'menu.entry.regionalgroups',
        menuItems: [
          { url: 'communitiesGermany', menuTitle: 'menu.entry.Germany' },
          { url: 'communitiesAustria', menuTitle: 'menu.entry.Austria' },
          { url: 'communitiesSwitzerland', menuTitle: 'menu.entry.Swiss' },
          { url: 'international', menuTitle: 'menu.entry.international' }
        ]
      }]
    }
  }
}

</script>
