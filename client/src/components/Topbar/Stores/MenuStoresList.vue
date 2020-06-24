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
    >
      <a
        :href="$url('store', store.id)"
        role="menuitem"
        class="dropdown-item text-truncate"
      >
        <span
          :id="`store_marker_${store.id}`"
          v-b-tooltip="store.pickupStatus === 0 ? '' : $i18n(tooltipId(store.pickupStatus))"
          class="store-status fas fa-circle"
          :class="statusClass(store.pickupStatus)"
        >&nbsp;</span>
        <span class="store-name">
          {{ store.name }}
        </span>
        <span
          v-if="store.isManaging"
          v-b-tooltip="$i18n('store.tooltip_managing')"
          class="text-muted is-managing"
        >
          <i class="fas fa-cog text-right" />
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

<style scoped>
  .store-status {
    margin-left: -1rem;
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
  .is-managing {
    position: absolute;
    right: 0.5rem;
  }
  .loader-container {
    text-align: center;
  }
</style>
