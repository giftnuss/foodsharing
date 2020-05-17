<template>
  <b-nav-item-dropdown
    id="dropdown-stores"
    v-b-tooltip="$i18n('menu.entry.your_stores')"
    no-caret
    lazy
  >
    <template v-slot:button-content>
      <i class="fas fa-shopping-cart" />
    </template>
    <menu-stores-list /> <!-- This is an own component in order to be lazy loaded -->
    <a
      v-if="mayAddStore"
      :href="$url('storeAdd')"
      role="menuitem"
      class="dropdown-item"
    >
      <small><i class="fas fa-plus" /> {{ $i18n('store.add_new_store') }} </small>
    </a>
    <a
      :href="$url('storeList')"
      role="menuitem"
      class="dropdown-item"
    >
      <small><i class="fas fa-list" /> {{ $i18n('store.all_of_my_stores') }} </small>
    </a>
  </b-nav-item-dropdown>
</template>

<script>
import MenuStoresList from '@/components/Topbar/Stores/MenuStoresList'
export default {
  components: { MenuStoresList },
  props: {
    mayAddStore: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style lang="scss" scoped>
  .bootstrap .badge-info {
    background-color: #f5f5b5;
  }
</style>
