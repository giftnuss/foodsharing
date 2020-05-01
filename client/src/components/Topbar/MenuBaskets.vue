<template>
  <b-nav-item-dropdown
    id="dropdown-baskets"
    ref="dropdown"
    v-b-tooltip="$i18n('basket.title')"
    :no-caret="!showLabel"
    class="topbar-baskets"
  >
    <template v-slot:button-content>
      <i class="fas fa-shopping-basket" />
      <span v-if="showLabel">
        {{ $i18n('basket.title') }}
      </span>
    </template>
    <div class="list-group">
      <p
        v-if="!baskets.length"
        class="dropdown-header"
      >
        {{ $i18n('basket.my_list_empty') }}
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
          {{ $i18n('basket.all') }}
        </a>
        <a
          href="#"
          class="btn btn-sm btn-secondary"
          @click="openBasketCreationForm"
        >
          {{ $i18n('basket.add') }}
        </a>
      </div>
    </div>
  </b-nav-item-dropdown>
</template>
<script>
import MenuBasketsEntry from './MenuBasketsEntry'
import basketStore from '@/stores/baskets'

import { ajreq } from '@/script'
import dateFnsCompareDesc from 'date-fns/compareDesc'

export default {
  components: { MenuBasketsEntry },
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
      return this.baskets.slice().sort((a, b) => dateFnsCompareDesc(a.updatedAt, b.updatedAt))
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
