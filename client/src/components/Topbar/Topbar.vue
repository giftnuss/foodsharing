<template>
  <div
    id="topbar"
    class="bootstrap"
  >
    <b-navbar
      fixed="top"
      toggleable="sm"
      class="navbar-expand-md navbar-dark bg-primary"
    >
      <b-navbar-brand>
        <Logo :link-url="loggedIn ? $url('dashboard') : $url('home')" />
      </b-navbar-brand>

      <b-navbar-nav class="nav-row">
        <menu-item
          :url="$url('joininfo')"
          icon="fa-rocket"
          :title="$i18n('register.topbar')"
        />
        <menu-item
          id="login"
          icon="fa-rocket"
          title="Login"
        />
        <div class="bootstrap">
          <Login />
        </div>
      </b-navbar-nav>
      <b-navbar-toggle target="nav-collapse">
        <i :class="`fa fa-bars`" />
      </b-navbar-toggle>

      <b-collapse
        id="nav-collapse"
        is-nav
      >
        <menu-loggedout />
      </b-collapse>
    </b-navbar>
  </div>
</template>

<script>
import { BNavbarBrand, BNavbarToggle, BCollapse } from 'bootstrap-vue'
import Logo from './Logo'
import Login from './Login'
import MenuLoggedout from './MenuLoggedout'
import MenuItem from './MenuItem'

export default {
  components: {
    BCollapse,
    BNavbarToggle,
    BNavbarBrand,
    Login,
    MenuLoggedout,
    Logo,
    MenuItem
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
  }

}
</script>
<style lang="scss" scoped>
#topbar {
  height: 38px;
  nav {
    box-shadow: 0em 0em 5px 0px black;
  }
}
  .bootstrap .navbar-brand {
    padding: 0;
  }
 .nav-row {
  flex-direction: row!important;
  margin:0;
  display: flex;
  flex-grow: 1;
  .nav-item {
    margin-right: 3px;
    &:first-child {
      margin-left: auto;
    }
  }
  .login-popover {
    background-color: --var(beige);
    border: 1px solid rgb(83, 58, 32);
    .arrow {
      display: none;
    }
  }
}
/deep/ .navbar-collapse.show {
  &.show {
    // Only when menu is shown. Fixes problem that list of dropdown items is to long.
    max-height: 70vh;
    overflow: auto;
  }
  .nav-link i {
      width: 40px;
      text-align: center;
  }
}
</style>
