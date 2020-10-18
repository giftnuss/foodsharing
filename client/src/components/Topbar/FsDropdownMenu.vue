<template>
  <b-nav-item-dropdown
    v-if="show"
    v-b-tooltip="$i18n(menuTitle)"
    :right="right"
    :lazy="lazy"
    class="caret-beneath"
  >
    <template v-slot:button-content>
      <i
        v-if="icon"
        :class="`fas ${icon}`"
      />
      <slot name="heading-text">
        <span
          v-if="showMenuTitle"
          class="d-md-none"
        >{{ $i18n(menuTitle) }}</span>
      </slot>
    </template>
    <slot>
      <template v-for="heading in items">
        <h3
          :key="heading.heading"
          class="dropdown-header"
        >
          {{ $i18n(heading.heading) }}
        </h3>
        <a
          v-for="item in heading.menuItems"
          :key="item.url"
          :href="$url(item.url)"
          class="dropdown-item sub"
          role="menuitem"
          :target="item.target ? item.target : ''"
          :rel="item.target === '_blank' ? 'noopener noreferrer nofollow' : '' "
        >
          {{ $i18n(item.menuTitle) }}
        </a>
      </template>
    </slot>
  </b-nav-item-dropdown>
</template>
<script>
import MediaQueryMixin from '@/utils/VueMediaQueryMixin'

export default {
  mixins: [MediaQueryMixin],
  props: {
    menuTitle: {
      type: String,
      default: undefined
    },
    items: {
      type: Array,
      default: undefined
    },
    icon: {
      type: String,
      default: undefined
    },
    showMenuTitle: {
      type: Boolean,
      default: true
    },
    lazy: {
      type: Boolean,
      default: false
    },
    right: {
      type: Boolean,
      default: false
    },
    showOnlyOnMobile: { type: Boolean, default: false },
    hideOnlyOnMobile: { type: Boolean, default: false }
  },
  computed: {
    show () {
      if (this.hideOnlyOnMobile) {
        return !(this.wXS || this.wSM)
      } else if (this.showOnlyOnMobile) {
        return this.wXS || this.wSM
      }
      return true
    }
  }
}
</script>

<style lang="scss" scoped>
i {
  font-size: 1rem;
}
.caret-beneath /deep/ .dropdown-toggle {
  padding-bottom: 5px;
  text-align: center;
  &::after {
    display: flex;
    width: max-content;
    align-self: center;
    margin-left: auto;
    margin-right: auto;
  }
}

@media(max-width: 767px) {
  .collapse {
    .caret-beneath /deep/ .dropdown-toggle {
      text-align: unset;
      &::after {
        display: inline-block;
        margin-left: 0.255em;
        vertical-align: middle;
      }
    }
    .dropdown /deep/ .dropdown-menu {
      // Margin to have an indent in the burger menu.
      margin-left: 30px;
    }
  }
}
.dropdown {
  /deep/ .dropdown-menu {
    max-width: 300px;
    overflow-y: auto;
    box-shadow: 0 0 7px rgba(0, 0, 0, 0.3);
    // Fixes problem that list of dropdown items is to long.
    max-height: 70vh;
    overflow: auto;
    .scroll-container {
      max-height: 300px;
      min-height: 120px;
      overflow: auto;
    }
    .dropdown-item {
      font-size: 15px;
        i {
        display: inline-block;
        width: 1.7em;
        text-align: center;
        margin-left: -0.4em;
      }
    }
    .sub {
      padding-left: 2.2rem;
      font-size: 0.9rem;
    }
    .dropdown-header {
     font-weight: bold;
    }
  }
  @media (max-width: 575px) {
    position: initial;
    /deep/ .dropdown-menu {
      width: 100%;
      max-width: initial;
      top: 2.2em;
      .scroll-container {
        width: 100%;
      }
    }
  }
  /deep/ .badge {
    position: absolute;
    margin-top: -0.5em;
    margin-left: -0.7em;
  }
}
</style>
