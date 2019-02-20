<template>
  <nav-item-dropdown right :no-caret="!displayArrow">
      <template slot="button-content">
        <i class="fas fa-info " />
        <span v-if="displayText">
          Infos
        </span>
      </template>
      <a
        :href="$url('faq')"
        class="dropdown-item"
        role="menuitem"
      >
        F.A.Q.
      </a>        
      <a
        :href="$url('blog')"
        class="dropdown-item"
        role="menuitem"
      >
        Blog
      </a>
      <a
        :href="$url('wiki')"
        class="dropdown-item"
        role="menuitem"
      >
        Wiki
      </a>
      <a
        :href="$url('guide')"
        class="dropdown-item"
        role="menuitem"
      >
        Ratgeber
      </a>
      <a
        :href="$url('transparency')"
        class="dropdown-item"
        role="menuitem"
      >
        Transparenz
      </a>
      <a
        :href="$url('dataprivacy')"
        class="dropdown-item"
        role="menuitem"
      >
        Datenschutz
      </a>
      <a
        :href="$url('changelog')"
        class="dropdown-item"
        role="menuitem"
      >
        Changelog
      </a>        
    </nav-item-dropdown>
</template>
<script>
import NavItemDropdown from './NavItemDropdown'

export default {
  components: { NavItemDropdown },
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
  }
}
</script>