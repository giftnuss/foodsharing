<template>
  <b-nav-item
    v-b-tooltip.hover.bottom
    :class="{'d-none d-sm-inline-block': hideOnMobile}"
    :href="url"
    :title="title"
    :aria-label="title"
    @click="$emit('click')"
  >
    <i :class="`fas ${icon}`" />
    <span
      v-if="!hideTitleAlways"
      :class="{'d-md-none': !showTitleAlways, 'd-none d-md-inline-block': hideTitleMobile}"
    >
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
    showTitleAlways: { type: Boolean, default: false },
    hideTitleAlways: { type: Boolean, default: false },
    hideTitleMobile: { type: Boolean, default: false },
    hideOnMobile: { type: Boolean, default: false }
  }
}
</script>
