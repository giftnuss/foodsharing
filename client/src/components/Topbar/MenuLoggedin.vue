<template>
  <b-navbar-nav
    id="topbar-navright"
    class="ml-auto"
  >
    <menu-item
      :url="$url('map')"
      icon="fa-map-marker-alt"
      :title="$i18n('storelist.map')"
      :show-only-on-mobile="true"
    />

    <menu-admin
      v-if="someAdminRights"
      :is-orga-team="isOrgaTeam"
      :may="may"
    />
    <MenuBullhorn />
    <MenuInformation />
    <MenuEnvelope :display-mailbox="displayMailbox" />
    <menu-messages :hide-only-on-mobile="true" />
    <menu-bells :hide-only-on-mobile="true" />
    <menu-user
      :user-id="fsId"
      :avatar="image"
      :hide-only-on-mobile="true"
    />
  </b-navbar-nav>
</template>

<script>

import { VBTooltip, BNavbarNav } from 'bootstrap-vue'
import MenuBullhorn from './MenuBullhorn'
import MenuInformation from './MenuInformation'
import MenuEnvelope from './MenuEnvelope'
import MenuItem from './MenuItem'
import MenuMessages from './Messages/MenuMessages'
import MenuBells from './Bells/MenuBells'
import MenuUser from './MenuUser'
import MenuAdmin from './MenuAdmin'

export default {
  components: { MenuAdmin, MenuBullhorn, MenuInformation, MenuEnvelope, BNavbarNav, MenuItem, MenuMessages, MenuBells, MenuUser },
  directives: { VBTooltip },
  props: {
    displayMailbox: {
      type: Boolean,
      default: false
    },
    fsId: {
      type: Number,
      default: null
    },
    image: {
      type: String,
      default: ''
    },
    isOrgaTeam: {
      type: Boolean,
      default: true
    },
    may: {
      type: Object,
      default: () => ({})
    }
  },
  computed: {
    someAdminRights () {
      return this.isOrgaTeam || this.may.administrateBlog || this.may.editQuiz || this.may.handleReports || this.may.editContent || this.may.editFAQ || this.may.manageMailboxes || this.may.administrateNewsletterEmail || this.may.administrateRegions
    }
  }
}
</script>
