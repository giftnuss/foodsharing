<template>
  <b-nav-item-dropdown
    v-b-tooltip="$i18n(menuTitle)"
    right
  >
    <template v-slot:button-content>
      <i :class="`fas ${icon}`" />
      <span class="d-sm-none">{{ $i18n(menuTitle) }}</span>
    </template>
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
      >{{ $i18n(item.menuTitle) }}</a>
    </template>
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
    }
  }
}
</script>

<style lang="scss" scoped>
</style>
