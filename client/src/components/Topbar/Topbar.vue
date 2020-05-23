<template>
  <div
    id="topbar"
    class="bootstrap"
  >
    <b-navbar
      fixed="top"
      toggleable="md"
      class="navbar-expand-md navbar-dark bg-primary"
    >
      <b-container fluid="xl">
        <b-navbar-brand>
          <Logo :link-url="loggedIn ? $url('dashboard') : $url('home')" />
        </b-navbar-brand>

        <!-- When not logged in -->
        <b-navbar-nav
          v-if="!loggedIn"
          class="nav-row justify-content-md-end"
        >
          <menu-item
            :url="$url('joininfo')"
            icon="fa-rocket"
            :title="$i18n('register.topbar')"
            :show-title-always="true"
          />
          <menu-item
            id="login"
            icon="fa-rocket"
            title="Login"
            :show-title-always="true"
          />
          <div class="bootstrap">
            <Login />
          </div>
        </b-navbar-nav>

        <!-- When logged in -->
        <logged-in-fixed-nav
          v-if="loggedIn"
          :has-fs-role="hasFsRole"
          :regions="regions"
          :stores="stores"
          :working-groups="workingGroups"
          :may-add-stores="may.addStore"
          @openSearch="searchOpen = !searchOpen"
        />

        <search
          v-if="hasFsRole"
          :show-on-mobile="searchOpen"
        />

        <b-navbar-toggle target="nav-collapse">
          <i class="fa fa-bars" />
        </b-navbar-toggle>

        <b-collapse
          id="nav-collapse"
          is-nav
        >
          <menu-loggedout v-if="!loggedIn" />
          <menu-loggedin
            v-if="loggedIn"
            :display-mailbox="mailbox"
            :fs-id="fsId"
            :image="image"
          />
        </b-collapse>
      </b-container>
    </b-navbar>
  </div>
</template>

<script>
import { BNavbarBrand, BNavbarToggle, BCollapse } from 'bootstrap-vue'
import Logo from './Logo'
import Login from './Login'
import MenuLoggedout from './MenuLoggedout'
import MenuLoggedin from './MenuLoggedin'
import MenuItem from './MenuItem'
import LoggedInFixedNav from './LoggedInFixedNav'
import Search from './Search/Search'

export default {
  components: {
    BCollapse,
    BNavbarToggle,
    BNavbarBrand,
    Login,
    MenuLoggedout,
    Logo,
    MenuItem,
    LoggedInFixedNav,
    MenuLoggedin,
    Search
  },
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
    stores: {
      type: Array,
      default: () => []
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
  data () {
    return {
      searchOpen: false
    }
  }
}
</script>
<style lang="scss" scoped>
#topbar {
  height: 50px;
  nav {
    box-shadow: 0em 0em 5px 0px black;
  }
  /deep/ .dropdown-menu {
    box-shadow: 0 0 7px rgba(0, 0, 0, 0.3)
  }
}
  .bootstrap .navbar-brand {
    padding: 0;
    margin-right: 3px;
  }
 .nav-row {
  flex-direction: row!important;
  margin:0;
  display: flex;
  flex-grow: 1;
  align-items: center;
  justify-content: space-evenly;
  /deep/ .nav-item {
    padding-right: 6px;
  }
  .login-popover {
    background-color: --var(beige);
    border: 1px solid rgb(83, 58, 32);
    .arrow {
      display: none;
    }
  }
}

/deep/ .dropdown-menu {
  // Fixes problem that list of dropdown items is to long.
  max-height: 70vh;
  overflow: auto;
}

/deep/ .navbar-collapse {
  &.show {
    // Only when menu is shown. Fixes problem that list of dropdown items is to long.
    max-height: 70vh;
    overflow: auto;
    .dropdown-menu  {
      max-height: initial;
    }
  }
  &.show .nav-link i, &.collapsing .nav-link i {
      width: 40px;
      text-align: center;
  }
  order: 2;
}

</style>
