<template>
  <b-nav-item-dropdown
    v-if="show"
    ref="dropdown-menu"
    v-b-tooltip="$i18n(menuTitle)"
    :right="right"
    :lazy="lazy"
    class="caret-beneath"
  >
    <template #button-content>
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
    <div class="scroll-container">
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
    </div>
    <div class="d-flex actions">
      <!-- eslint-disable-next-line vue/max-attributes-per-line -->
      <slot name="actions" :hide="closeDropdownMenu" />
    </div>
  </b-nav-item-dropdown>
</template>
<script>
import MediaQueryMixin from '@/utils/VueMediaQueryMixin'

export default {
  mixins: [MediaQueryMixin],
  props: {
    menuTitle: {
      type: String,
      default: undefined,
    },
    items: {
      type: Array,
      default: undefined,
    },
    icon: {
      type: String,
      default: undefined,
    },
    showMenuTitle: {
      type: Boolean,
      default: true,
    },
    lazy: {
      type: Boolean,
      default: false,
    },
    right: {
      type: Boolean,
      default: false,
    },
    showOnlyOnMobile: { type: Boolean, default: false },
    hideOnlyOnMobile: { type: Boolean, default: false },
  },
  computed: {
    show () {
      if (this.hideOnlyOnMobile) {
        return !(this.wXS || this.wSM)
      } else if (this.showOnlyOnMobile) {
        return this.wXS || this.wSM
      }
      return true
    },
  },
  methods: {
    closeDropdownMenu () {
      this.$refs['dropdown-menu'].hide()
    },
  },
}
</script>

<style lang="scss" scoped>
i {
  font-size: 1rem;
}
.caret-beneath ::v-deep .dropdown-toggle {
  padding-bottom: 5px;
  text-align: center;
  &::after {
    display:flex;
    width: max-content;
    align-self: center;
    margin-left: auto;
    margin-right: auto;
    visibility: hidden;
  }
  &:hover::after {
    visibility: visible;
  }
  @media (hover: none) {
    &::after {
      visibility: visible;
    }
  }
}

@media(max-width: 767px) {
  .collapse {
    .caret-beneath ::v-deep .dropdown-toggle {
      text-align: unset;
      &::after {
        display: inline-block;
        margin-left: 0.255em;
        vertical-align: middle;
      }
      &::after {
        visibility: visible;
      }
    }
    .dropdown ::v-deep .dropdown-menu {
      // Margin to have an indent in the burger menu.
      margin-left: 30px;
    }
  }
}
.dropdown {
  &.list-with-actions ::v-deep .dropdown-menu {
    padding: 0;
  }
  ::v-deep .dropdown-menu {
    // Bug of chrome: https://bugs.chromium.org/p/chromium/issues/detail?id=957946
    background-clip: unset;

    max-width: 300px;
    box-shadow: 0 0 7px rgba(0, 0, 0, 0.3);
    .scroll-container {
      // LibSass is deprecated: https://github.com/sass/libsass/issues/2701
      max-height: unquote("min(340px, 70vh)");
    // Fixes problem that list of dropdown items is to long.
      overflow: auto;
    }
    .dropdown-item {
        i {
        display: inline-block;
        width: 1.7em;
        text-align: center;
        margin-left: -0.4em;
      }
    }
    .actions .btn{
      flex: 1 1 auto;
      margin: 0 1px;
    }
    .sub {
      padding-left: 2.2rem;
      font-size: 0.9rem;
    }
    .dropdown-header {
     font-weight: bold;
    }
    .group .dropdown-header {
      color: black;
    }
  }
  @media (max-width: 575px) {
    position: initial;
    ::v-deep .dropdown-menu {
      width: 100%;
      max-width: initial;
      top: 2.2em;
      .scroll-container {
        width: 100%;
      }
    }
  }
  ::v-deep .badge {
    position: absolute;
    margin-top: -0.5em;
    margin-left: -0.7em;
  }
}
</style>
