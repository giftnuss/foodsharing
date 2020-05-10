<template>
  <div
    id="topbar"
    :class="{bootstrap:true, loggedIn}"
  >
    <b-navbar
      toggleable="md"
      class="navbar fixed-top navbar-expand-md navbar-dark bg-primary "
    >
      <div
        v-if="!loggedIn"
        class="container"
      >
        <div id="topbar-navleft">
          <Logo
            :link-url="$url('home')"
          />
          <login v-if="!isMobile" />
          <menu-loggedout
            v-if="isMobile"
            :is-mobile="ui.wXS || ui.wMD"
          />
        </div>
        <login v-if="isMobile" />
        <menu-loggedout
          v-if="!isMobile"
          :is-mobile="ui.wXS || ui.wMD"
        />
      </div>

      <div
        v-if="loggedIn"
        class="container"
      >
        <div id="topbar-navleft">
          <Logo
            :link-url="$url('dashboard')"
            class="small"
          />
          <b-navbar-nav class="flex-row no-collapse">
            <li
              v-if="!hasFsRole"
              class="nav-item ml-2"
            >
              <a
                :href="$url('upgradeToFs')"
                class="nav-link"
              >
                <i class="fas fa-rocket" />
                <small v-if="isMobile">
                  {{ $i18n('foodsaver.upgrade_to') }}
                </small>
                <span v-else>
                  {{ $i18n('foodsaver.upgrade_to') }}
                </span>
              </a>
            </li>
            <menu-region
              v-if="hasFsRole"
              :regions="regions"
              :active-region-id="activeRegionId"
            />
            <menu-groups
              v-if="hasFsRole"
              :working-groups="workingGroups"
            />
            <menu-stores
              v-if="hasFsRole"
              :may-add-store="may.addStore"
            />
            <menu-baskets :show-label="!hasFsRole && !isMobile" />
            <li
              v-if="!isMobile"
              v-b-tooltip.hover="$i18n('storelist.map')"
              class="nav-item"
            >
              <a
                :href="$url('map')"
                :aria-label="$i18n('storelist.map')"
                class="nav-link"
              >
                <i class="fas fa-map-marker-alt" />
                <span v-if="!loggedIn || !hasFsRole">
                  {{ $i18n('storelist.map') }}
                </span>
              </a>
            </li>
            <menu-messages v-if="isMobile" />
            <menu-bells v-if="isMobile" />
          </b-navbar-nav>
          <b-navbar-toggle
            v-if="!hasFsRole"
            target="nav_collapse"
            class="ml-2"
          />
        </div>

        <search v-if="hasFsRole" />
        <b-navbar-toggle
          v-if="hasFsRole"
          target="nav_collapse"
          class="ml-2 pr-1"
        />

        <b-collapse
          id="nav_collapse"
          is-nav
        >
          <b-navbar-nav class="ml-auto">
            <li
              v-b-tooltip.hover="$i18n('home.home')"
              class="nav-item"
            >
              <a
                :href="$url('home')"
                :aria-label="$i18n('home.title')"
                class="nav-link"
              >
                <i class="fas fa-home" />
                <span class="d-md-none">
                  {{ $i18n('home.title') }}
                </span>
              </a>
            </li>
            <li
              v-if="isMobile"
              v-b-tooltip.hover="$i18n('storelist.map')"
              class="nav-item"
            >
              <a
                :href="$url('map')"
                class="nav-link"
              >
                <i class="fas fa-map-marker-alt" />
                <span class="d-md-none">
                  {{ $i18n('storelist.map') }}
                </span>
              </a>
            </li>
            <menu-admin
              v-if="someAdminRights"
              :is-orga-team="isOrgaTeam"
              :may="may"
            />
            <MenuBullhorn
              :display-arrow="false"
              :display-text="ui.wXS || ui.wSM"
            />
            <MenuInformation
              :display-arrow="false"
              :display-text="ui.wXS || ui.wSM"
            />
            <MenuEnvelope
              :display-arrow="false"
              :display-mailbox="mailbox"
              :display-text="ui.wXS || ui.wSM"
            />

            <menu-messages v-if="!isMobile" />
            <menu-bells v-if="!isMobile" />
            <menu-user
              :user-id="fsId"
              :avatar="image"
              :is-mobile="isMobile"
            />
          </b-navbar-nav>
        </b-collapse>
      </div>
    </b-navbar>
  </div>
</template>

<script>
import ui from '@/stores/ui'
import { VBTooltip, BCollapse, BNavbarToggle } from 'bootstrap-vue'

import MenuRegion from './MenuRegion'
import MenuStores from './Stores/MenuStores'
import MenuGroups from './MenuGroups'
import MenuBaskets from './Baskets/MenuBaskets'
import MenuBullhorn from './MenuBullhorn'
import MenuInformation from './MenuInformation'
import MenuEnvelope from './MenuEnvelope'
import MenuAdmin from './MenuAdmin'
import MenuMessages from './Messages/MenuMessages'
import MenuBells from './Bells/MenuBells'
import MenuUser from './MenuUser'
import Search from './Search/Search'
import Login from './Login'
import MenuLoggedout from './MenuLoggedout'
import Logo from './Logo'

export default {
  components: {
    BCollapse,
    BNavbarToggle,
    MenuLoggedout,
    MenuRegion,
    MenuStores,
    MenuGroups,
    MenuBaskets,
    MenuBullhorn,
    MenuInformation,
    MenuEnvelope,
    MenuAdmin,
    MenuMessages,
    MenuBells,
    MenuUser,
    Search,
    Login,
    Logo
  },
  directives: { VBTooltip },
  props: {
    fsId: {
      type: Number,
      default: null
    },
    loggedIn: {
      type: Boolean,
      default: true
    },
    image: {
      type: String,
      default: ''
    },
    mailbox: {
      type: Boolean,
      default: true
    },
    hasFsRole: {
      type: Boolean,
      default: true
    },
    isOrgaTeam: {
      type: Boolean,
      default: true
    },
    may: {
      type: Object,
      default: () => ({})
    },
    regions: {
      type: Array,
      default: () => []
    },
    workingGroups: {
      type: Array,
      default: () => []
    }
  },
  computed: {
    someAdminRights () {
      return this.isOrgaTeam || this.may.administrateBlog || this.may.editQuiz || this.may.handleReports || this.may.editContent || this.may.editFAQ || this.may.manageMailboxes || this.may.administrateNewsletterEmail || this.may.administrateRegions
    },
    isMobile () {
      return this.ui.wSM || this.ui.wXS
    },
    ui () {
      return ui
    },
    activeRegionId () {
      return ui.activeRegionId
    }
  }
}
</script>

<style lang="scss" scoped>
#topbar {
    .navbar {
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
    }

    .container {
        max-width: 1100px;
    }

    @media (max-width: 630px) {
        #topbar-navleft {
            width: 100%;
        }
    }
    .navbar-nav {
        align-items: center;
    }
    .navbar-collapse.collapse.show, .navbar-collapse.collapsing {
        &.show {
          // Only when menu is shown. Fixes problem that list of dropdown items is to long.
          max-height: 70vh;
          overflow: auto;
        }
        .navbar-nav {
            align-items: start;
        }
    }
}
#topbar-navleft {
    display:flex;
    align-items: center;
    flex-grow: 1;
    margin-right: 1em;
}
</style>

<style lang="scss">
#topbar {
    .nav-link {
        white-space: nowrap;
        padding: 0.4em 0.5em;
        i {
            font-size: 1.25em;
        }
    }
    @media (max-width: 700px) {
        .nav-link {
            padding: 0.4em 0.2em;
            i {
                font-size: 1em;
            }
        }
    }

    .no-collapse {
        display:flex;
        flex-grow: 1;
        flex-direction: row;

        .nav-link {
            padding-right: 0.5rem;
            padding-left: 0.5rem;
        }
        .dropdown-menu {
            position: absolute;
            @media (max-width: 500px) {
              position: fixed;
            }
        }
    }
    .dropdown-toggle {
        white-space: nowrap;
    }
    .nav-item > a > .badge {
        position: absolute;
        margin-top: -0.5em;
        margin-left: -0.7em;
    }
    ul {
        margin-left: 0;
    }

    // dropdown styles
    .dropdown-item i {
        display: inline-block;
        width: 1.7em;
        text-align: center;
        margin-left: -0.4em;
    }
    .dropdown-menu .sub .dropdown-item {
        font-size: 0.8em;
        padding-left: 3em;
        font-weight: normal;
    }
     .dropdown-item.sub {
        padding-left: 2.5em;

     }
    .dropdown-item {
        font-weight: bold;
        font-size: 0.9em;
    }

    .dropdown-menu {
        max-height: 420px;
        max-width: 300px;
        overflow-y: auto;
    }
    .dropdown-menu .scroll-container {
        max-height: 300px;
        min-height: 120px;
        width: 270px;
        overflow-y: scroll;
    }

    @media (max-width: 500px) {
        .dropdown {
            position: initial;
        }
        .dropdown-menu {
            width: 100%;
            max-width: initial;
            top: 2.2em;
        }
        .dropdown-menu .scroll-container {
            width: 100%;
        }
        #search-results {
            top: 5em;
            width: 100%;
            left: 0 !important;
        }
    }
    .navbar-collapse.collapsing, .navbar-collapse.show {
        .nav-link i {
            width: 40px;
            text-align: center;
        }
        li {
            width: 100%;
        }
    }
}

// following is applied on the initial <div> before the vue component gets injected
// it shows an brown bar as a placeholder for the actual topbar
#vue-topbar {
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
    background-color: #533a20 !important;
    position: fixed;
    top: 0;
    height: 37px;
    width: 100%;
    z-index: 1200;
}

#topbar {
  @media (max-width: 823px) {
    height: 90px;

    &.loggedIn {
      height: 37px;
    }
  }

  @media (min-width: 993px) {
    height: 45px;

    &.loggedIn {
      height: 37px;
    }
  }
  @media (max-height: 320px), (max-width: 415px) {
    height: 130px;

    &.loggedIn {
      height: 80px;
    }
  }
}
</style>
