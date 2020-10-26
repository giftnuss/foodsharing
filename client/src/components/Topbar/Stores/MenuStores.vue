<template>
  <fs-dropdown-menu
    id="dropdown-stores"
    menu-title="menu.entry.your_stores"
    icon="fa-shopping-cart"
    lazy
  >
    <template v-slot:heading-text>
      <span />
    </template>
    <menu-stores-list /> <!-- This is an own component in order to be lazy loaded -->
    <a
      v-if="mayAddStore"
      :href="$url('storeAdd')"
      role="menuitem"
      class="dropdown-item"
    >
      <small><i class="fas fa-plus" /> {{ $i18n('storeedit.add-new') }} </small>
    </a>
    <a
      :href="$url('storeList')"
      role="menuitem"
      class="dropdown-item"
    >
      <small><i class="fas fa-list" /> {{ $i18n('store.all_of_my_stores') }} </small>
    </a>
  </fs-dropdown-menu>
</template>

<script>
import MenuStoresList from '@/components/Topbar/Stores/MenuStoresList'
import FsDropdownMenu from '../FsDropdownMenu'
export default {
  components: { MenuStoresList, FsDropdownMenu },
  props: {
    mayAddStore: {
      type: Boolean,
      default: false,
    },
  },
}
</script>

<style lang="scss" scoped>
  .bootstrap .badge-info {
    background-color: #f5f5b5;
  }
</style>
