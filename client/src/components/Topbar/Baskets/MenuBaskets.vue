<template>
  <fs-dropdown-menu
    ref="dropdown"
    menu-title="basket.title"
    icon="fa-shopping-basket"
    class="list-with-actions topbar-baskets"
  >
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
    </div>
    <template v-slot:actions>
      <b-btn
        :href="$url('baskets')"
        secondary
        size="sm"
      >
        {{ $i18n('basket.all') }}
      </b-btn>
      <b-btn
        href="#"
        secondary
        size="sm"
        @click="openBasketCreationForm"
      >
        {{ $i18n('basket.add') }}
      </b-btn>
    </template>
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
      default: true,
    },
  },
  computed: {
    baskets () {
      return basketStore.baskets
    },
    basketsSorted () {
      return this.baskets.slice().sort((a, b) => dateFnsCompareDesc(a.updatedAt, b.updatedAt))
    },
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
        fid: userId,
      })
    },
  },
}
</script>

<style lang="scss">

</style>
