<template>
  <b-nav-item-dropdown
    v-b-tooltip="$i18n(menuTitle)"
    right
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
    }
  }
}
</script>

<style lang="scss" scoped>
i {
  font-size: 1rem;
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
