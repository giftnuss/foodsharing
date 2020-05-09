<template>
  <b-nav-item
    v-b-tooltip.hover.bottom
    :href="url"
    :title="title"
    :aria-label="title"
  >
    <i :class="`fas ${icon}`" />
    <span :class="{'d-sm-none': !showOnMobile}">
      {{ title }}
    </span>
  </b-nav-item>
</template>

<script>
import { VBTooltip } from 'bootstrap-vue'

export default {
  directives: { VBTooltip },
  props: {
    url: { type: String, default: undefined },
    icon: { type: String, default: undefined },
    title: { type: String, default: undefined },
    showOnMobile: { type: Boolean, default: false }
  }
}
</script>
