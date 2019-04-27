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
            :title="$i18n('pickuplist.day_tab')"
          >
            <b-pagination
              v-model="currentPageDaily"
              :total-rows="pickupDataDailyTab.length"
              :per-page="perPage"
              aria-controls="pickupDaily-table"
            />
            <b-table
              id="pickupDaily-table"
              striped
              hover
              small
              bordered
              responsive
              :current-page="currentPageDaily"
              :per-page="perPage"
              :fields="fields"
              :items="pickupDataDailyTab"
            />
          </b-tab>
          <b-tab
            :title="$i18n('pickuplist.week_tab')"
          >
            <b-pagination
              v-model="currentPageWeekly"
              :total-rows="pickupDataWeeklyTab.length"
              :per-page="perPage"
              aria-controls="pickupWeekly-table"
            />
            <b-table
              id="pickupWeekly-table"
              striped
              hover
              small
              bordered
              responsive
              :fields="fields"
              :items="pickupDataWeeklyTab"
              :current-page="currentPageWeekly"
              :per-page="perPage"
            />
          </b-tab>
          <b-tab
            :title="$i18n('pickuplist.month_tab')"
          >
            <b-pagination
              v-model="currentPageMonthly"
              :total-rows="pickupDataMonthlyTab.length"
              :per-page="perPage"
              aria-controls="pickupMonthly-table"
            />
            <b-table
              striped
              hover
              small
              bordered
              responsive
              :fields="fields"
              :items="pickupDataMonthlyTab"
              :current-page="currentPageMonthly"
              :per-page="perPage"
            />
          </b-tab>
        </b-tabs>
      </div>
    </div>
  </div>
</template>

<script>

import bPagination from '@b/components/pagination/pagination'
import bTable from '@b/components/table/table'
import bTabs from '@b/components/tabs/tabs'
import bTab from '@b/components/tabs/tab'

export default {
  components: { bTable, bTabs, bTab, bPagination },
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
      currentPageDaily: 1,
      currentPageWeekly: 1,
      currentPageMonthly: 1,
      perPage: 14,
      fields: {
        time: {
          label: this.$i18n('pickuplist.time_table_header'),
          sortable: true
        },
        NumberOfStores: {
          label: this.$i18n('pickuplist.NumberOfStores_table_header'),
          sortable: true
        },
        NumberOfAppointments: {
          label: this.$i18n('pickuplist.NumberOfAppointments_table_header'),
          sortable: true
        },
        NumberOfSlots: {
          label: this.$i18n('pickuplist.NumberOfSlots_table_header'),
          sortable: true
        },
        NumberOfFoodsavers: {
          label: this.$i18n('pickuplist.NumberOfFoodSavers_table_header'),
          sortable: true
        }
      }
    }
  }
}
</script>
