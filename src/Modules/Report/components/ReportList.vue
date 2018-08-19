<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <div class="card-header text-white bg-primary">
        Alle Betriebe aus dem Bezirk {{ regionName }} (<span v-if="reports.length !== reportsFiltered.length">{{ reportsFiltered.length }} von </span>{{ reports.length }})
      </div>
      <div
        v-if="reports.length"
        class="card-body p-0">

        <b-table
          :fields="fields"
          :items="reportsFiltered"
          :current-page="currentPage"
          :per-page="perPage"
          responsive
        >
        <template slot="avatar" slot-scope="row">
          <Avatar
              :url="row.item.rp_photo"
              :sleep-status="0"
              :size="75"
            />
        </template>

        <template slot="actions" slot-scope="row">
          <b-button size="sm" @click.stop="row.toggleDetails">
            {{ row.detailsShowing ? 'Hide' : 'Show' }} Report
          </b-button>
        </template>
        <template slot="row-details" slot-scope="row">
          <h4>{{row.item.fs_name}} {{ row.item.fs_nachname }}</h4>
          <p>{{row.item.rp_content}}</p>
        </template>
        </b-table>
        <div class="float-right p-1 pr-3">
          <b-pagination
            :total-rows="reportsFiltered.length"
            :per-page="perPage"
            v-model="currentPage"
            class="my-0" />
        </div>
      </div>
      <div
        v-else
        class="card-body">
        Es sind noch keine Betriebe eingetragen
      </div>
      <b-modal id="modalInfo" :title="modalInfo.title" ok-only>
        <pre>{{ modalInfo.content }}</pre>
      </b-modal>
    </div>
  </div>
</template>

<script>
import bTable from '@b/components/table/table'
import bPagination from '@b/components/pagination/pagination'
import bFormSelect from '@b/components/form-select/form-select'
import bModal from '@b/components/modal/modal'
import bButton from '@b/components/button/button'
import bTooltip from '@b/directives/tooltip/tooltip'
import * as api from '@/api/report'

import Avatar from '@/components/Avatar'

const noLocale = /^[\w-.\s,]*$/

export default {
  components: { Avatar, bTable, bPagination, bFormSelect, bModal, bButton },
  directives: { bTooltip },
  props: {
    regionId: {
      type: Number,
      default: null,
    },
    regionName: {
      type: String,
      default: ''
    }
  },
  data () {
    return {
      currentPage: 1,
      perPage: 100,
      filterText: '',
      filterStatus: null,
      reports: [],
      fields: {
        avatar: {
          labael: 'Avatar'
        },
        fs_stadt: {
          label: 'Stadt',
          sortable: true
        },
        fs_name: {
          label: 'Name',
          sortable: true
        },
        fs_nachname: {
          label: 'Nachname',
          sortable: true
        },
        rp_name: {
          label: 'Report',
          sortable: true
        },
        rp_nachname: {
          label: '',
          sortable: true
        },
        b_name: {
          label: '',
          sortable: true
        },
        actions: {
          label: 'Actions'
        }
      },
      modalInfo: { title: '', content: '' },
      info (item, index, button) {
        this.modalInfo.title = `${item.fs_name} ${item.fs_nachname}`
        this.modalInfo.content = JSON.stringify(item, null, 2)
        this.$root.$emit('bv::show::modal', 'modalInfo', button)
      },

    }
  },
  async created () {
    const reports = await api.getReportsByRegion(this.regionId);
    Object.assign(this, {
      reports: reports,
    });
  },
  computed: {
    reportsFiltered: function () {
      if (!this.filterText.trim() && !this.filterStatus) return this.reports
      let filterText = this.filterText ? this.filterText.toLowerCase() : null
      return this.reports.filter((store) => {
        return (
          (!this.filterStatus || store.status === this.filterStatus) &&
          (!filterText || (
            store.name.toLowerCase().indexOf(filterText) !== -1 ||
            store.address.toLowerCase().indexOf(filterText) !== -1 ||
            store.region.toLowerCase().indexOf(filterText) !== -1
          ))
        )
      })
    }
  }
}
</script>
