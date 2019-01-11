<template>
  <nav-item-dropdown
    ref="dropdown"
    :no-caret="!showLabel"
    tooltip="Essenskörbe"
    class="topbar-baskets"
  >
    <template slot="button-content">
      <i class="fas fa-shopping-basket" />
      <span v-if="showLabel">
        Essenskörbe
      </span>
    </template>
    <div class="list-group">
      <p
        v-if="!baskets.length"
        class="dropdown-header"
      >
        Du hast keine Essenskörbe eingetragen
      </p>
      <div
        v-else
        class="scroll-container"
      >
        <menu-baskets-entry
          v-for="basket in basketsSorted"
          :key="basket.id"
          :basket="basket"
          @basketRemove="openRemoveBasketForm"
        />
      </div>
      <div class="list-grou-item p-2 text-center">
        <a
          :href="$url('baskets')"
          class="btn btn-sm btn-secondary"
        >
          Alle Essenskörbe
        </a>
        <a
          href="#"
          class="btn btn-sm btn-secondary"
          @click="openBasketCreationForm"
        >
          Essenskorb anlegen
        </a>
      </div>
    </div>
  </nav-item-dropdown>
</template>
<script>
import NavItemDropdown from './NavItemDropdown'
import MenuBasketsEntry from './MenuBasketsEntry'
import basketStore from '@/stores/baskets'

import { ajreq } from '@/script'

export default {
  components: {
    NavItemDropdown,
    MenuBasketsEntry
  },
  props: {
    showLabel: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    baskets () {
      return basketStore.baskets
    },
    basketsSorted () {
      return this.baskets.slice().sort((a, b) => b.updatedAt.localeCompare(a.updatedAt))
    }
  },
  created () {
    basketStore.loadBaskets()
  },
  methods: {
    openBasketCreationForm () {
      this.$refs.dropdown.visible = false
      ajreq('newBasket', { app: 'basket' })
    },
    openRemoveBasketForm (basketId, userId) {
      this.$refs.dropdown.visible = false
      ajreq('removeRequest', {
        app: 'basket',
        id: basketId,
        fid: userId
      })
    }
  }
}
</script>

<style lang="scss">
.topbar-baskets {
    .dropdown-menu {
        overflow-x: hidden;
        padding: 0;
    }
}
</style>
