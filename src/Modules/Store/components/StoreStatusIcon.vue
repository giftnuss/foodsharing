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
      required: true,
    },
  },
  computed: {
    description () {
      switch (this.status) {
        case 1: // CooperationStatus::NO_CONTACT
        case 2: // CooperationStatus::IN_NEGOTIATION
          return i18n('storestatus.2')
        case 3: // CooperationStatus::COOPERATION_STARTING
        case 5: // CooperationStatus::COOPERATION_ESTABLISHED
          return i18n('storestatus.5')
        case 4: // CooperationStatus::DOES_NOT_WANT_TO_WORK_WITH_US
        case 7: // CooperationStatus::PERMANENTLY_CLOSED
          return i18n('storestatus.4')
        case 6: // CooperationStatus::GIVES_TO_OTHER_CHARITY
          return i18n('storestatus.6')
        default: // unclear
          return i18n('storestatus.0')
      }
    },
  },
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

    &[data-status="4"],
    &[data-status="7"] {
        color: #df4b4d;
    }

    &[data-status="6"] {
        color: #4765f3;
    }
}
</style>
