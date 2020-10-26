<template>
  <b-nav-item
    v-if="show"
    v-b-tooltip.hover.bottom
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
import MediaQueryMixin from '@/utils/VueMediaQueryMixin'

export default {
  directives: { VBTooltip },
  mixins: [MediaQueryMixin],
  props: {
    url: { type: String, default: undefined },
    icon: { type: String, default: undefined },
    title: { type: String, default: undefined },
    showTitleAlways: { type: Boolean, default: false },
    hideTitleAlways: { type: Boolean, default: false },
    hideTitleMobile: { type: Boolean, default: false },
    hideOnMobile: { type: Boolean, default: false },
    showOnlyOnMobile: { type: Boolean, default: false },
  },
  computed: {
    show () {
      if (this.hideOnMobile) {
        return !(this.wXS || this.wSM)
      } else if (this.showOnlyOnMobile) {
        return this.wXS || this.wSM
      }
      return true
    },
  },
}
</script>

<style lang="scss" scoped>
i {
  font-size: 1rem;
}
</style>
