<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <div
        class="card-header text-white bg-primary"
      >
        {{ $i18n('ageBandList.header_for_district', {bezirk: regionName}) }}
      </div>
      <div>
        <b-tabs
          pills
          card
        >
          <b-tab
            :title="$i18n('ageBandList.district_tab')"
            active
          >
            <b-table
              :fields="fields"
              :items="ageBandDataTab"
              :sort-by="sortBy"
              striped
              hover
              small
              caption-top
            />
          </b-tab>
          <b-tab
            :title="$i18n('ageBandList.home_district_tab')"
          >
            <b-table
              :fields="fields"
              :items="ageBandDataHomeDistrictTab"
              :sort-by="sortBy"
              striped
              hover
              small
              caption-top
            />
          </b-tab>
        </b-tabs>
      </div>
    </div>
  </div>
</template>

<script>

import { BTable, BTabs, BTab } from 'bootstrap-vue'

export default {
  components: { BTable, BTabs, BTab },
  props: {
    regionName: {
      type: String,
      default: ''
    },
    ageBandDataTab: {
      type: Array,
      default: () => []
    },
    ageBandDataHomeDistrictTab: {
      type: Array,
      default: () => []
    }
  },
  data () {
    return {
      sortBy: 'ageBand',
      fields: {
        ageBand: {
          label: this.$i18n('ageBandList.ageBand'),
          sortable: true
        },
        NumberOfAgeband: {
          label: this.$i18n('ageBandList.NumberOfAgeband'),
          sortable: true
        }
      }
    }
  }
}
</script>
