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
      v-if="stores.length || !loaded"
      class="dropdown-divider"
    />
  </div>
</template>

<script>
import vueStore from '@/stores/stores'
import { BTooltip } from 'bootstrap-vue'

export default {
  components: { BTooltip },
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

<style scoped>
  .fa-circle {
    margin-left: -1em;
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
  .loader-container {
    text-align: center;
  }
</style>
