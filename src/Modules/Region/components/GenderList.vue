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
              title="Gesamtbezirk"
              active
            >
              <b-table
                striped
                hover
                small
                :items="genderDataTab"
                caption-top
              >
                <template slot="table-caption">
                  Aufteilung des Merkmales "Geschlecht" auf alle foodsaver, die dem Bezirk beigetreten sind.
                </template>
              </b-table>
            </b-tab>
            <b-tab
              title="Stammbezirk"
            >
              <b-table
                striped
                hover
                small
                :items="genderDataHomeDistrictTab"
                caption-top
              >
                <template slot="table-caption">
                  Aufteilung des Merkmales "Geschlecht" auf die foodsaver, die als Stammbezirk eingetragen sind
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
    }
  }
}
</script>
