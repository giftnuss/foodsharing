<template>
  <b-nav-item-dropdown
    v-b-tooltip="$i18n(menuTitle)"
    right
    :lazy="lazy"
    class="caret-beneath"
  >
    <template v-slot:button-content>
      <i :class="`fas ${icon}`" />
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

export default {
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
  .collapse .caret-beneath /deep/ .dropdown-toggle {
    text-align: unset;
    &::after {
      display: inline-block;
      margin-left: 0.255em;
      vertical-align: middle;
    }
  }
}
.dropdown {
  @media (max-width: 575px) {
    position: initial;
    /deep/ .dropdown-menu {
      width: 100%;
    }
  }
}
</style>
