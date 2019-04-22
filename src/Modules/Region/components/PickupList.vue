<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <div
        class="card-header text-white bg-primary"
      >
        {{ $i18n('pickuplist.header_for_district', {bezirk: regionName}) }}
      </div>
      <div>
        <b-tabs
          pills
          card
        >
          <b-tab
            title="Tag"
            active
          >
            <P />
            <b-table
              striped
              hover
              small
              bordered
              :items="pickupDataDailyTab"
            />
          </b-tab>
          <b-tab
            title="Woche"
            active
          >
            <P />
            <b-table
              striped
              hover
              small
              bordered
              :items="pickupDataWeeklyTab"
            />
          </b-tab>
          <b-tab
            title="Monat"
            active
          >
            <P />
            <b-table
              striped
              hover
              small
              bordered
              :items="pickupDataMonthlyTab"
            />
          </b-tab>
        </b-tabs>
      </div>
    </div>
  </div>
</template>

<script>

import bTable from '@b/components/table/table'
import bTabs from '@b/components/tabs/tabs'
import bTab from '@b/components/tabs/tab'

export default {
  components: { bTable, bTabs, bTab },
  props: {
    regionName: {
      type: String,
      default: ''
    },
    pickupDataDailyTab: {
      type: Array,
      default: () => []
    },
    pickupDataWeeklyTab: {
      type: Array,
      default: () => []
    },
    pickupDataMonthlyTab: {
      type: Array,
      default: () => []
    }
  },
  data () {
    return {
    }
  }
}
</script>
