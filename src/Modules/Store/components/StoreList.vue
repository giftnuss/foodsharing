<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <div class="card-header text-white bg-primary">
        Alle Betriebe aus dem Bezirk {{ regionName }} 
        <span>
          {{ $i18n('memberlist.some_in_all', {some: storesFiltered.length, all: stores.length}) }}
        </span>
      </div>
      <div
        v-if="stores.length"
        class="card-body p-0"
      >
        <div class="form-row p-1 ">
          <div class="col-2 text-center">
            <label class=" col-form-label col-form-label-sm">
              Filtern nach...
            </label>
          </div>
          <div class="col-4">
            <input
              v-model="filterText"
              type="text"
              class="form-control form-control-sm"
              placeholder="Name/Adresse/Bezirk"
            >
          </div>
          <div class="col-3">
            <b-form-select
              v-model="filterStatus"
              :options="statusOptions"
              size="sm"
            />
          </div>
          <div class="col">
            <button
              v-b-tooltip.hover
              type="button"
              class="btn btn-sm"
              title="Filter leeren"
              @click="clearFilter"
            >
              <i class="fas fa-times" />
            </button>
          </div>
        </div>

        <b-table
          :fields="fields"
          :items="storesFiltered"
          :current-page="currentPage"
          :per-page="perPage"
          :sort-compare="compare"
          small
          hover
          responsive
        >
          <template
            slot="status"
            slot-scope="data"
          >
            <div class="text-center">
              <StoreStatusIcon :status="data.value" />
            </div>
          </template>
          <template
            slot="name"
            slot-scope="data"
          >
            <a
              :href="$url('store', data.item.id)"
              class="ui-corner-all"
            >
              {{ data.value }}
            </a>
          </template>
        </b-table>
        <div class="float-right p-1 pr-3">
          <b-pagination
            v-model="currentPage"
            :total-rows="storesFiltered.length"
            :per-page="perPage"
            class="my-0"
          />
        </div>
      </div>
      <div
        v-else
        class="card-body"
      >
        Es sind noch keine Betriebe eingetragen
      </div>
    </div>
  </div>
</template>

<script>
import { optimizedCompare } from '@/utils'
import bTable from '@b/components/table/table'
import bPagination from '@b/components/pagination/pagination'
import bFormSelect from '@b/components/form-select/form-select'
import bTooltip from '@b/directives/tooltip/tooltip'
import StoreStatusIcon from './StoreStatusIcon.vue'

const noLocale = /^[\w-.\s,]*$/

export default {
  components: { bTable, bPagination, bFormSelect, StoreStatusIcon },
  directives: { bTooltip },
  props: {
    regionName: {
      type: String,
      default: ''
    },
    stores: {
      type: Array,
      default: () => []
    }
  },
  data () {
    return {
      currentPage: 1,
      perPage: 20,
      filterText: '',
      filterStatus: null,
      fields: {
        status: {
          label: 'Status',
          tdClass: 'status',
          sortable: true
        },
        name: {
          label: 'Name',
          sortable: true
        },
        address: {
          label: 'Anschrift',
          sortable: true
        },
        added: {
          label: 'Eingetragen',
          sortable: true
        },
        region: {
          label: 'Bezirk',
          sortable: true
        }
      },
      statusOptions: [
        { value: null, text: 'Status' },
        { value: 1, text: 'Noch kein Kontakt' },
        { value: 2, text: 'Verhandlungen laufen' },
        { value: 3, text: 'In Kooperation' },
        { value: 4, text: 'Will nicht kooperieren' },
        { value: 6, text: 'Wirft nichts weg' }
      ],
    }
  },
  computed: {
    storesFiltered: function () {
      if (!this.filterText.trim() && !this.filterStatus) return this.stores
      let filterText = this.filterText ? this.filterText.toLowerCase() : null
      return this.stores.filter((store) => {
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
  },
  methods: {
    compare: optimizedCompare,
    
    clearFilter () {
      this.filterStatus = null
      this.filterText = ''
    }
  }
}
</script>
