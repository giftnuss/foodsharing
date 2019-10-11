<template>
  <nav-item-dropdown
    tooltip="Deine Betriebe"
    no-caret
  >
    <template slot="button-content">
      <i class="fas fa-shopping-cart" />
    </template>
    <div
      v-for="store in stores"
      :key="store.id"
    >
      <a
        :href="$url('store', store.id)"
        role="menuitem"
        class="dropdown-item text-truncate"
      >
        <i
          v-if="store.pickupStatus == 1"
          class="fas fa-circle"
          style="color: yellow;"
        />
        <i
          v-else-if="store.pickupStatus == 2"
          class="fas fa-circle"
          style="color: orange;"
        />
        <i
          v-else-if="store.pickupStatus == 3"
          class="fas fa-circle"
          style="color: red;"
        />
        <i
          v-else
          class="fas fa-circle"
          style="color: white; filter: alpha(opacity=0);"
        />
        {{ store.name }}
      </a>
    </div>
    <div
      v-if="stores.length && mayAddStore"
      class="dropdown-divider"
    />
    <a
      v-if="mayAddStore"
      :href="$url('storeAdd')"
      role="menuitem"
      class="dropdown-item"
    >
      <small><i class="fas fa-plus" /> Neuen Betrieb anlegen</small>
    </a>
  </nav-item-dropdown>
</template>
<script>
import NavItemDropdown from './NavItemDropdown'
export default {
  components: {
    NavItemDropdown
  },
  props: {
    stores: {
      type: Array,
      default: () => []
    },
    mayAddStore: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style>

</style>
