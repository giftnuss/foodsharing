<template>
  <nav-item-dropdown
    tooltip="Deine Betriebe"
    no-caret
  >
    <template slot="button-content">
      <span>
        <i class="fas fa-shopping-cart" />
        <i
          :style="circleStyle(globalPickupStatus)"
          class="fas fa-circle fa-stack-1x fa-sup"
        />
      </span>
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
          :style="circleStyle(store.pickupStatus)"
          style="margin-left: -1.5em;"
          class="fas fa-circle"
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
    circleStyle (pickupStatus) {
      const colors = ['#f2cd00', '#f4922f', '#dc3545']
      return {
        color: colors[pickupStatus - 1],
        visibility: pickupStatus > 0 ? 'visible' : 'hidden'
      }
    }
  }
}
</script>

<style lang="scss">
  .fa-stack {
    vertical-align: bottom;
  }
  .fa-sup {
    margin: -.5em 0px 0px .5em;
    text-shadow: 0 0 1px black;
    font-size: 1em !important;
  }
</style>
