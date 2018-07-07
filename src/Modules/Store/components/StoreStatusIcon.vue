<template>
  <span
    v-b-tooltip.hover
    :title="description"
    :data-status="status"
    class="status">
    <i class="fa fa-circle" />
  </span>
</template>

<script>
import bTooltip from '@b/directives/tooltip/tooltip'

export default {
  directives: { bTooltip },
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
          return 'Es besteht noch kein Kontakt'
        case 2:
          return 'Verhandlungen laufen'
        case 3:
        case 5:
          return 'Betrieb kooperiert bereits'
        case 4:
          return 'Will nicht kooperieren'
        case 6:
          return 'Spendet an Tafel etc. und wirft nichts weg'
        default:
          return 'Status unklar'
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
