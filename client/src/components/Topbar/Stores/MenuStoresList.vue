<template>
  <div>
    <div
      v-if="!loaded"
      class="loader-container"
    >
      <img src="/img/469.gif"> <!-- 469.gif is the file name of the spinning apple -->
    </div>
    <div
      v-for="store in stores"
      v-else
      :key="store.id"
      class="store-list-dropdown"
    >
      <a
        :href="$url('store', store.id)"
        role="menuitem"
        class="dropdown-item d-inline-flex align-items-baseline"
      >
        <i
          :id="`store_marker_${store.id}`"
          v-b-tooltip="store.pickupStatus === 0 ? '' : $i18n(tooltipId(store.pickupStatus))"
          class="store-status fas fa-fw fa-circle flex-grow-0 flex-shrink-0"
          :class="statusClass(store.pickupStatus)"
        />
        <span class="store-name flex-grow-1 flex-shrink-1 text-truncate">
          {{ store.name }}
        </span>
        <span
          v-if="store.isManaging"
          v-b-tooltip="$i18n('store.tooltip_managing')"
          class="text-muted is-managing flex-grow-0 flex-shrink-0"
        >
          <i class="fas fa-fw fa-cog text-right" />
        </span>
      </a>
    </div>
    <div
      v-if="stores.length || !loaded"
      class="dropdown-divider"
    />
  </div>
</template>

<script>
import vueStore from '@/stores/stores'

export default {
  data () {
    return { loaded: false }
  },
  computed: {
    stores () {
      return vueStore.stores || []
    }
  },
  async created () {
    this.loadStores()
  },
  methods: {
    async loadStores () {
      if (vueStore.stores === null) {
        await vueStore.loadStores()
      }
      this.loaded = true
    },
    badgeClass (pickupStatus) {
      const classes = ['badge-info', 'badge-info', 'badge-warning', 'badge-danger']
      return classes[pickupStatus]
    },
    statusClass (pickupStatus) {
      const classes = ['invisible', 'status-info', 'status-warning', 'status-danger']
      return classes[pickupStatus]
    },
    tooltipId (pickupStatus) {
      const ids = ['', 'store.tooltip_yellow', 'store.tooltip_orange', 'store.tooltip_red']
      return ids[pickupStatus]
    }
  }
}
</script>

<style lang="scss" scoped>
.store-list-dropdown .store-status {
  margin-left: -1.25rem !important; // needs !important thanks to the `#topbar .dropdown-item i` rule
  margin-right: .25rem;

  &.status-info {
    color: var(--fs-beige);
  }
  &.status-warning {
    color: var(--warning);
  }
  &.status-danger {
    color: var(--danger);
  }
}

.is-managing {
  margin-right: -1rem;
  padding-left: .25rem;
}

.loader-container {
  text-align: center;
}
</style>
