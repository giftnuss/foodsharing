<template>
  <span
    v-b-tooltip.hover
    :title="description"
    :data-status="status"
    class="status"
  >
    <i class="fas fa-circle" />
  </span>
</template>

<script>

import { VBTooltip } from 'bootstrap-vue'
import i18n from '@/i18n'

export default {
  directives: { VBTooltip },
  props: {
    status: {
      type: Number,
      required: true
    }
  },
  computed: {
    description () {
      switch (this.status) {
        case 1:
          return i18n('storestatusicon.nocontact')
        case 2:
          return i18n('storestatusicon.inprogress')
        case 3:
        case 5:
          return i18n('storestatusicon.cooperating')
        case 4:
          return i18n('storestatusicon.nocooperation')
        case 6:
          return i18n('storestatusicon.nowaste')
        default:
          return i18n('storestatusicon.unclear')
      }
    }
  }
}
</script>

<style scoped lang="scss">
.status {
    width: 50px;
    text-align: center;

    &[data-status="1"] {
        color: grey;
    }

    &[data-status="2"] {
        color: #f6e257;
    }

    &[data-status="3"],
    &[data-status="5"] {
        color: #79aa51;
    }

    &[data-status="4"] {
        color: #df4b4d;
    }

    &[data-status="6"] {
        color: #4765f3;
    }
}
</style>
