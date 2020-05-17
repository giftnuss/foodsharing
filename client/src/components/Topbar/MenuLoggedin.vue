<template>
  <b-navbar-nav
    id="topbar-navright"
    class="ml-auto"
  >
    <menu-item
      :url="$url('map')"
      icon="fa-map-marker-alt"
      :title="$i18n('storelist.map')"
      class="d-sm-none"
    />

    <MenuBullhorn />
    <MenuInformation />
    <MenuEnvelope :display-mailbox="displayMailbox" />
    <menu-messages class="d-none d-md-inline-block" />
    <menu-bells class="d-none d-md-inline-block" />
    <menu-user
      :user-id="fsId"
      :avatar="image"
    />
  </b-navbar-nav>
</template>

<script>

import { VBTooltip, BNavbarNav } from 'bootstrap-vue'
import MenuBullhorn from './MenuBullhorn'
import MenuInformation from './MenuInformation'
import MenuEnvelope from './MenuEnvelope'
import MenuItem from './MenuItem'
import MenuMessages from './MenuMessages'
import MenuBells from './MenuBells'
import MenuUser from './MenuUser'

export default {
  components: { MenuBullhorn, MenuInformation, MenuEnvelope, BNavbarNav, MenuItem, MenuMessages, MenuBells, MenuUser },
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
    }
  }
}
</script>
