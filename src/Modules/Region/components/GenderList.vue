<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <div
        class="card-header text-white bg-primary"
      >
        {{ $i18n('genderlist.header_for_district', {bezirk: regionName}) }}
      </div>
      <div>
        <b-card no-body>
          <b-tabs
            pills
            card
          >
            <b-tab
              :title="$i18n('genderlist.district_tab')"
              active
            >
              <b-table
                :fields="fields"
                :items="genderDataTab"
                :sort-by="sortBy"
                :sort-desc="sortDesc"
                striped
                hover
                small
                caption-top
              >
                <template slot="table-caption">
                  {{ $i18n('genderlist.gender_district_table_caption') }}
                </template>
              </b-table>
            </b-tab>
            <b-tab
              :title="$i18n('genderlist.home_district_tab')"
            >
              <b-table
                :fields="fields"
                :items="genderDataHomeDistrictTab"
                :sort-by="sortBy"
                :sort-desc="sortDesc"
                striped
                hover
                small
                caption-top
              >
                <template slot="table-caption">
                  {{ $i18n('genderlist.gender_home_district_table_caption') }}
                </template>
              </b-table>
            </b-tab>
          </b-tabs>
        </b-card>
      </div>
    </div>
  </div>
</template>

<script>

import { BCard, BTable, BTabs, BTab } from 'bootstrap-vue'

export default {
  components: { BCard, BTable, BTabs, BTab },
  props: {
    regionName: {
      type: String,
      default: ''
    },
    genderDataTab: {
      type: Array,
      default: () => []
    },
    genderDataHomeDistrictTab: {
      type: Array,
      default: () => []
    }
  },
  data () {
    return {
      sortBy: 'gender',
      sortDesc: true,
      fields: [
        {
          key: 'gender',
          label: this.$i18n('genderlist.gender_table_header'),
          formatter: item => {
            switch (item) {
              case 0:
                return this.$i18n('genderlist.gender_not_selected')
              case 1:
                return this.$i18n('genderlist.gender_male')
              case 2:
                return this.$i18n('genderlist.gender_female')
              case 3 :
                return this.$i18n('genderlist.gender_divers')
              default :
                return this.$i18n('genderlist.gender_not_selected')
            }
          },
          sortable: true
        },
        {
          key: 'NumberOfGender',
          label: this.$i18n('genderlist.number_table_header'),
          sortable: true
        }
      ]
    }
  }
}
</script>
