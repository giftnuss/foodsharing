<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <div
        class="card-header text-white bg-primary"
      >
        {{ $i18n('genderlist.header_for_district', {bezirk: regionName}) }}
      </div>
      <div>
        <b-table
          striped
          hover
          :items="genderDataTab"
        />
      </div>
    </div>
  </div>
</template>

<script>

import bTable from '@b/components/table/table'

export default {
  components: { bTable },
  props: {
    regionName: {
      type: String,
      default: ''
    },
    genderDataTab: {
      type: Array,
      default: () => []
    },
    isWorkGroup: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
    }
  }
}
</script>
