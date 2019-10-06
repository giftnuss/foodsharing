
<template>
  <li :class="dropdownClasses">
    <a
      ref="toggle"
      :id="`dropdown_${_uid}`"
      :class="toggleClasses"
      :aria-expanded="visible ? 'true' : 'false'"
      :aria-label="tooltip"
      @click="buttonClick"
      @mouseover="() => hover = true"
      @mouseout="() => hover = false"
      @keydown="buttonClick"
      href="#"
      aria-haspopup="true"
    >
      <slot name="button-content" />
    </a>
    <b-tooltip
      ref="tooltip"
      :target="`dropdown_${_uid}`"
      :show="hover && !visible"
      :triggers="[]"
    >
      {{ tooltip }}
    </b-tooltip>
    <div
      ref="menu"
      :class="menuClasses"
      @keydown="onKeydown"
    >
      <slot />
    </div>
  </li>
</template>

<script>
// modified version of boostrap-vue's b-nav-item-dropdown
import idMixin from 'bootstrap-vue/esm/mixins/id'
import dropdownMixin from 'bootstrap-vue/esm/mixins/dropdown'
import { BTooltip } from 'bootstrap-vue'

export default {
  components: { BTooltip },
  mixins: [idMixin, dropdownMixin],
  props: {
    noCaret: {
      type: Boolean,
      default: false
    },
    extraToggleClasses: {
      // Extra Toggle classes
      type: String,
      default: ''
    },
    extraMenuClasses: {
      // Extra Menu classes
      type: String,
      default: ''
    },
    role: {
      type: String,
      default: 'menu'
    },
    tooltip: {
      type: String,
      default: null
    }
  },
  data () {
    return {
      hover: false
    }
  },
  computed: {
    isNav () {
      // Signal to dropdown mixin that we are in a navbar
      return true
    },
    dropdownClasses () {
      return [
        'nav-item',
        'b-nav-dropdown',
        'dropdown',
        this.dropup ? 'dropup' : '',
        this.visible ? 'show' : ''
      ]
    },
    toggleClasses () {
      return [
        'nav-link',
        this.noCaret ? '' : 'dropdown-toggle',
        this.disabled ? 'disabled' : '',
        this.extraToggleClasses ? this.extraToggleClasses : ''
      ]
    },
    menuClasses () {
      return [
        'dropdown-menu',
        this.right ? 'dropdown-menu-right' : 'dropdown-menu-left',
        this.visible ? 'show' : '',
        this.extraMenuClasses ? this.extraMenuClasses : ''
      ]
    }
  },
  methods: {
    buttonClick (event) {
      if (this.visible) {
        this.hover = false
      }
      this.toggle(event)
    }
  }

}
</script>
