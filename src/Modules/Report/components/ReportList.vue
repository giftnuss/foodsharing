<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <div class="card-header text-white bg-primary">
        Alle Reports (<span v-if="reports.length">{{ reports.length }}</span>)
      </div>
      <div
        v-if="reports.length"
        class="card-body p-0">

        <b-table
          :fields="fields"
          :items="reports"
          :current-page="currentPage"
          :per-page="perPage"
          responsive
        >
          <template
            slot-scope="row"
            slot="avatar">
            <div class="avatars">
              <Avatar
                :url="row.item.fs_photo"
                :sleep-status="0"
                :size="35"
              />
              <Avatar
                :url="row.item.rp_photo"
                :sleep-status="0"
                :size="35"
              />
            </div>
          </template>

          <template
            slot-scope="row"
            slot="actions">
            <b-button
              size="sm"
              @click.stop="row.toggleDetails">
              {{ row.detailsShowing ? 'Hide' : 'Show' }}
            </b-button>
          </template>
          <template
            slot-scope="row"
            slot="row-details">
            <div class="report">
              <p><strong>{{ $i18n('reports.report_id') }}:</strong> {{ row.item.rp_id }}</p>
              <p><strong>{{ $i18n('reports.time') }}:</strong> {{ row.item.time }}</p>
              <p><strong>{{ $i18n('reports.about') }}</strong><a :href="`/profile/${row.item.fs_id}`"> {{ row.item.fs_name }} {{ row.item.fs_nachname }}</a></p>
              <p><strong>{{ $i18n('reports.from') }}:</strong><a :href="`/profile/${row.item.rp_id}`"> {{ row.item.rp_name }} {{ row.item.rp_nachname }}</a></p>
              <p><strong>{{ $i18n('reports.ground') }}:</strong> {{ row.item.tvalue }}</p>
              <p><strong>{{ $i18n('reports.message') }}:</strong> {{ row.item.msg }}</p>
            </div>
          </template>
        </b-table>
        <div class="float-right p-1 pr-3">
          <b-pagination
            :total-rows="reports.length"
            :per-page="perPage"
            v-model="currentPage"
            class="my-0" />
        </div>
      </div>
      <div
        v-else
        class="card-body">
        Es sind noch keine Meldungen vorhanden
      </div>
    </div>
  </div>
</template>

<script>
import bTable from '@b/components/table/table'
import bPagination from '@b/components/pagination/pagination'
import bFormSelect from '@b/components/form-select/form-select'
import bButton from '@b/components/button/button'
import * as api from '@/api/report'

import Avatar from '@/components/Avatar'

export default {
  components: { Avatar, bTable, bPagination, bFormSelect, bButton },
  props: {
    regionId: {
      type: String,
      default: null
    },
    regionName: {
      type: String,
      default: ''
    }
  },
  data () {
    return {
      currentPage: 1,
      perPage: 50,
      reports: [],
      fields: {
        avatar: {
          label: ''
        },
        fs_stadt: {
          label: this.$i18n('reports.city'),
          sortable: true
        },
        time: {
          label: this.$i18n('reports.time'),
          sortable: true
        },
        fs_name: {
          label: this.$i18n('reports.about_first_name'),
          sortable: true
        },
        fs_nachname: {
          label: this.$i18n('reports.about_last_name'),
          sortable: true
        },
        rp_name: {
          label: this.$i18n('reports.from_first_name'),
          sortable: true
        },
        rp_nachname: {
          label: this.$i18n('reports.from_last_name'),
          sortable: true
        },
        b_name: {
          label: this.$i18n('reports.region'),
          sortable: true
        },
        actions: {
          label: ''
        }
      }
    }
  },
  async created () {
    const reports = await api.getReportsByRegion(this.regionId)
    Object.assign(this, {
      reports
    })
  }
}
</script>
<style>
  .avatars {
    display: flex;
  }
  .avatars div {
    margin-right: 5px;
  }

</style>
