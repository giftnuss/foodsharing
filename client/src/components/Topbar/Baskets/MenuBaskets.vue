<template>
  <fs-dropdown-menu
    id="dropdown-baskets"
    ref="dropdown"
    menu-title="basket.title"
    icon="fa-shopping-basket"
    class="topbar-baskets"
  >
    <template v-slot:heading-text>
      <span class="regionName text-truncate d-none d-sm-inline-block">
        {{ activeRegion ? activeRegion.name : $i18n('terminology.regions') }}
      </span>
    </template>
    <template v-slot:heading-text>
      <span
        v-if="showLabel"
        class="d-none d-sm-inline-block"
      >
        {{ $i18n('basket.title') }}
      </span>
      <span v-else />
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
      <div class="btn-group special btn-group-sm">
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
  </fs-dropdown-menu>
</template>
<script>
import MenuBasketsEntry from './MenuBasketsEntry'
import basketStore from '@/stores/baskets'
import FsDropdownMenu from '../FsDropdownMenu'

import { ajreq } from '@/script'
import dateFnsCompareDesc from 'date-fns/compareDesc'

export default {
  components: { MenuBasketsEntry, FsDropdownMenu },
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

.btn-group.special {
  display: flex;
}

.special .btn {
  flex: 1
}
</style>
