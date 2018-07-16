
<template>
    <li :class="this.dropdownClasses">

        <a 
            :class="this.toggleClasses"
            ref="toggle"
            :id="`dropdown_${_uid}`"
            href="#"
            aria-haspopup="true"
            :aria-expanded="this.visible ? 'true' : 'false'"
            @click="this.buttonClick"
            @mouseover="() => hover = true"
            @mouseout="() => hover = false"
            @keydown="this.buttonClick"
            :aria-label="tooltip"
        >
            <slot name="button-content"></slot>
        </a>
        <b-tooltip ref="tooltip" :target="`dropdown_${_uid}`" :show="hover && !visible" :triggers="[]">
          {{ tooltip }}
        </b-tooltip>
        <div 
            :class="this.menuClasses"
            ref="menu"
            @mouseover="onMouseOver"
            @keydown="onKeydown"
        >
            <slot></slot>
        </div>
    </li>
</template>


<script>
// modified version of boostrap-vue's b-nav-item-dropdown
import idMixin from '@b/mixins/id'
import dropdownMixin from '@b/mixins/dropdown'
export default {
  mixins: [idMixin, dropdownMixin],
  data() {
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
    buttonClick(event) {
      if(this.visible) {
        this.hover = false
      }
      this.toggle(event)
    }
  },
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
  }
    
}
</script>
