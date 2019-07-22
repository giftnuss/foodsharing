<template>
  <li>
    <button
      v-if="allowJoin && !allowRemove"
      v-b-tooltip
      @click="$emit('join')"
      :title="$i18n('pickup.take_empty_slot')"
      class="btn"
    >
      <i class="fa fa-question" />
    </button>

    <button
      v-else-if="allowRemove && !allowJoin"
      v-b-tooltip
      @click="$emit('remove')"
      @mouseover="hover = true"
      @mouseout="hover = false"
      :title="$i18n('pickup.slot_remove')"
      class="btn"
    >
      <i :class="`fa ${hover ? 'fa-times' : 'fa-question'}`" />
    </button>

    <nav-item-dropdown
      v-else-if="allowJoin && allowRemove"
      extra-toggle-classes="btn p-1 filled"
      size="sm"
      no-caret
      variant="primary"
    >
      <template slot="button-content">
        <i class="fa fa-question" />
      </template>
      <b-dropdown-item @click="$emit('join')">
        <i class="fa fa-check-circle mr-1" /> {{ $i18n('pickup.take_empty_slot') }}
      </b-dropdown-item>
      <b-dropdown-item @click="$emit('remove')">
        <i class="fa fa-times-circle mr-1" /> {{ $i18n('pickup.slot_remove') }}
      </b-dropdown-item>
    </nav-item-dropdown>

    <button
      v-else
      class="btn"
      disabled
    >
      <i class="fa fa-question" />
    </button>
  </li>
</template>

<script>

import { VBTooltip, BDropdownItem } from 'bootstrap-vue'
import NavItemDropdown from '@/components/Topbar/NavItemDropdown'

export default {
  components: { NavItemDropdown, BDropdownItem },
  directives: { VBTooltip },
  props: {
    allowJoin: {
      type: Boolean,
      default: false
    },
    allowRemove: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      hover: false
    }
  }
}
</script>

<style scoped>

</style>
