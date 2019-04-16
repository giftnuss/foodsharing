<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <div
        v-else
        class="card-header text-white bg-primary"
      >
        {{ $i18n('genderlist.header_for_district', {bezirk: regionName}) }}
      </div>
    </div>
  </div>
</template>

<script>
  import { optimizedCompare } from '@/utils'
  import bTable from '@b/components/table/table'
  import bPagination from '@b/components/pagination/pagination'
  import bTooltip from '@b/directives/tooltip/tooltip'

  export default {
    components: { bTable, bPagination },
    directives: { bTooltip },
    props: {
      regionName: {
        type: String,
        default: ''
      },
      genderData: {
        type: Array,
        default: () => []
      },
      isWorkGroup: {
        type: Boolean,
        default: false
      }
    },
  }
</script>
