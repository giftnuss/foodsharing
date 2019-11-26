<template>
  <nav-item-dropdown
    tooltip="Deine Betriebe"
    no-caret
  >
    <template slot="button-content">
      <i class="fas fa-shopping-cart" />
      <span
        v-if="globalPickupStatus>0"
        :class="'badge badge-pill '+badgeClass(globalPickupStatus)"
      >&nbsp;</span>
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
        <span
          :class="'badge badge-pill '+badgeClass(store.pickupStatus)"
          :style="badgeVisibility(store.pickupStatus)"
        >&nbsp;</span>
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
      <small><i class="fas fa-plus" /> {{ $i18n('store.add_new_store') }} </small>
    </a>
    <a
      :href="$url('storeList')"
      role="menuitem"
      class="dropdown-item"
    >
      <small><i class="fas fa-list" /> {{ $i18n('store.all_of_my_stores') }} </small>
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
  },
  computed: {
    globalPickupStatus () {
      let status = 0
      for (const store of this.stores) {
        status = Math.max(status, store.pickupStatus)
      }
      return status
    }
  },
  methods: {
    badgeClass (pickupStatus) {
      const classes = ['badge-info', 'badge-info', 'badge-warning', 'badge-danger']
      return classes[pickupStatus]
    },
    badgeVisibility (pickupStatus) {
      return {
        visibility: pickupStatus > 0 ? 'visible' : 'hidden'
      }
    }
  }
}
</script>

<style lang="scss">
  .badge {
    margin-left: -1.8em;
  }
  .bootstrap .badge-info {
    background-color: #f5f5b5;
  }
</style>
