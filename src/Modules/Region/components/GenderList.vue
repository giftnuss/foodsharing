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
                striped
                hover
                small
                :fields="fields"
                :items="genderDataTab"
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
                striped
                hover
                small
                :fields="fields"
                :items="genderDataHomeDistrictTab"
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
      fields: {
        gender: {
          label: this.$i18n('genderlist.gender_table_header'),
          formatter: item => {
            switch (item) {
              case 0:
                return this.$i18n('genderlist.gender_divers')
              case 1:
                return this.$i18n('genderlist.gender_male')
              case 2:
                return this.$i18n('genderlist.gender_female')
              default :
                return this.$i18n('genderlist.gender_divers')
            }
          },
          sortable: true
        },
        NumberOfGender: {
          label: 'Anzahl',
          sortable: true
        }
      }
    }
  }
}
</script>
