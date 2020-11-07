<template>
  <div
    id="topbar"
    class="bootstrap"
  >
    <b-navbar
      fixed="top"
      toggleable="md"
      class="navbar-expand-md"
      type="custom"
    >
      <b-container fluid="xl">
        <b-navbar-brand>
          <Logo :link-url="loggedIn ? $url('dashboard') : $url('home')" />
        </b-navbar-brand>

        <!-- When not logged in -->
        <b-navbar-nav
          v-if="!loggedIn"
          class="nav-row flex-row justify-content-md-end"
        >
          <menu-item
            :url="$url('joininfo')"
            icon="fa-rocket"
            :title="$i18n('register.topbar')"
            :show-title-always="true"
          />
          <menu-item
            id="login"
            :url="$url('login')"
            icon="fa-rocket"
            :title="$i18n('login.topbar')"
            :show-title-always="true"
          />
        </b-navbar-nav>

        <!-- When logged in -->
        <logged-in-fixed-nav
          v-if="loggedIn"
          :has-fs-role="hasFsRole"
          :regions="regions"
          :working-groups="workingGroups"
          :may-add-store="may.addStore"
          :avatar="avatar"
          :user-id="userId"
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
            :user-id="userId"
            :avatar="avatar"
            :may="may"
          />
        </b-collapse>
      </b-container>
    </b-navbar>
  </div>
</template>

<script>
import { BNavbarBrand, BNavbarToggle, BCollapse } from 'bootstrap-vue'
import Logo from './Logo'
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
    MenuLoggedout,
    Logo,
    MenuItem,
    LoggedInFixedNav,
    MenuLoggedin,
    Search,
  },
  props: {
    userId: {
      type: Number,
      default: null,
    },
    loggedIn: {
      type: Boolean,
      default: true,
    },
    avatar: {
      type: String,
      default: '',
    },
    mailbox: {
      type: Boolean,
      default: true,
    },
    hasFsRole: {
      type: Boolean,
      default: true,
    },
    may: {
      type: Object,
      default: () => ({}),
    },
    regions: {
      type: Array,
      default: () => [],
    },
    workingGroups: {
      type: Array,
      default: () => [],
    },
  },
  data () {
    return {
      searchOpen: false,
    }
  },
}
</script>
<style lang="scss" scoped>
#topbar {
  height: 50px;
  nav {
    box-shadow: 0em 0em 5px 0px black;
    background-color: var(--fs-beige);
    color: var(--primary);
  }
}
  .bootstrap .navbar-brand {
    padding: 0;
    margin-right: 3px;
  }
 .nav-row {
  margin:0;
  display: flex;
  flex-grow: 1;
  align-items: center;
  justify-content: space-evenly;
  ::v-deep .nav-item {
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

.navbar-toggler {
  color: var(--primary);
}

::v-deep .navbar-collapse {
  &.show {
    // Only when menu is shown. Fixes problem that list of dropdown items is to long.
    max-height: 70vh;
    overflow: auto;
    border-top: 1px solid var(--primary);
    .dropdown-menu .scroll-container  {
      max-height: initial;
    }
  }
  &.show .nav-link i, &.collapsing .nav-link i {
      width: 20px;
      margin-right: 10px;
      text-align: center;
  }
  order: 2;
}

</style>
