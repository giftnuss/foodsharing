<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <div
        class="card-header text-white bg-primary"
      >
        {{ $i18n('genderlist.header_for_district', {bezirk: regionName}) }}
      </div>
      <div>
        <b-tabs
          pills
          card
        >
          <b-tab
            title="Gesamtbezirk"
            active
          >
            <P />
            <P>&nbsp;&nbsp;Aufteilung des Merkmales "Geschlecht" auf alle foodsaver, die dem Bezirk beigetreten sind.</P>
            <b-table
              striped
              hover
              :items="genderDataTab"
            />
          </b-tab>
          <b-tab
            title="Stammbezirk"
          >
            <P />
            <p>&nbsp;&nbsp;Aufteilung des Merkmales "Geschlecht" auf die foodsaver, die als Stammbezirk eingetragen sind</p>
            <b-table
              striped
              hover
              :items="genderDataHomeDistrictTab"
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
    genderDataTab: {
      type: Array,
      default: () => []
    },
    genderDataHomeDistrictTab: {
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
