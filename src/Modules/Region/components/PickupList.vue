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
              :current-page="currentPageDaily"
              :per-page="perPage"
              :fields="fields"
              :items="pickupDataDailyTab"
              :sort-by="sortBy"
              :sort-desc="sortDesc"
              striped
              hover
              small
              bordered
              responsive
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
              :fields="fields"
              :items="pickupDataWeeklyTab"
              :current-page="currentPageWeekly"
              :per-page="perPage"
              :sort-by="sortBy"
              :sort-desc="sortDesc"
              striped
              hover
              small
              bordered
              responsive
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
              :fields="fields"
              :items="pickupDataMonthlyTab"
              :current-page="currentPageMonthly"
              :per-page="perPage"
              :sort-by="sortBy"
              :sort-desc="sortDesc"
              striped
              hover
              small
              bordered
              responsive
            />
          </b-tab>
          <b-tab
            :title="$i18n('pickuplist.year_tab')"
          >
            <b-pagination
              v-model="currentPageYearly"
              :total-rows="pickupDataYearlyTab.length"
              :per-page="perPage"
              aria-controls="pickupYearly-table"
            />
            <b-table
              :fields="fields"
              :items="pickupDataYearlyTab"
              :current-page="currentPageYearly"
              :per-page="perPage"
              :sort-by="sortBy"
              :sort-desc="sortDesc"
              striped
              hover
              small
              bordered
              responsive
            />
          </b-tab>
        </b-tabs>
      </div>
    </div>
  </div>
</template>

<script>

import { BPagination, BTable, BTabs, BTab } from 'bootstrap-vue'

export default {
  components: { BTable, BTabs, BTab, BPagination },
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
    },
    pickupDataYearlyTab: {
      type: Array,
      default: () => []
    }
  },
  data () {
    return {
      sortBy: 'time',
      sortDesc: true,
      currentPageDaily: 1,
      currentPageWeekly: 1,
      currentPageMonthly: 1,
      currentPageYearly: 1,
      perPage: 14,
      fields: [
        {
          key: 'time',
          label: this.$i18n('pickuplist.time_table_header'),
          sortable: true
        },
        {
          key: 'NumberOfStores',
          label: this.$i18n('pickuplist.NumberOfStores_table_header'),
          sortable: true
        },
        {
          key: 'NumberOfAppointments',
          label: this.$i18n('pickuplist.NumberOfAppointments_table_header'),
          sortable: true
        },
        {
          key: 'NumberOfSlots',
          label: this.$i18n('pickuplist.NumberOfSlots_table_header'),
          sortable: true
        },
        {
          key: 'NumberOfFoodsavers',
          label: this.$i18n('pickuplist.NumberOfFoodSavers_table_header'),
          sortable: true
        }
      ]
    }
  }
}
</script>
