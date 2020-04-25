<template>
  <b-nav-item-dropdown
    id="dropdown-stores"
    v-b-tooltip="$i18n('menu.entry.your_stores')"
    no-caret
  >
    <template v-slot:button-content>
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
          :id="`store_marker_${store.id}`"
          :class="'fas fa-circle '+statusClass(store.pickupStatus)"
          :style="badgeVisibility(store.pickupStatus)"
        >&nbsp;</span>
        {{ store.name }}
      </a>
      <b-tooltip
        v-if="store.pickupStatus>0"
        :target="`store_marker_${store.id}`"
      >
        {{ $i18n(tooltipId(store.pickupStatus)) }}
      </b-tooltip>
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
  </b-nav-item-dropdown>
</template>
<script>
import { BTooltip } from 'bootstrap-vue'
export default {
  components: { BTooltip },
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
    statusClass (pickupStatus) {
      const classes = ['status-info', 'status-info', 'status-warning', 'status-danger']
      return classes[pickupStatus]
    },
    badgeVisibility (pickupStatus) {
      return {
        visibility: pickupStatus > 0 ? 'visible' : 'hidden'
      }
    },
    tooltipId (pickupStatus) {
      const ids = ['store.tooltip_yellow', 'store.tooltip_orange', 'store.tooltip_red']
      return ids[pickupStatus - 1]
    }
  }
}
</script>

<style lang="scss" scoped>
  .fa-circle {
    margin-left: -1em;
  }
  .bootstrap .badge-info {
    background-color: #f5f5b5;
  }

  .status-info {
    color: #f5f5b5;
  }
  .status-warning {
    color: #ffc107;
  }
  .status-danger {
    color: #dc3545;
  }
</style>
