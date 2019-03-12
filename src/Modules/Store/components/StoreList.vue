<template>
  <div
    id="popoverContainer"
    class="container bootstrap"
  >
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
              placeholder="Name/Adresse"
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
          :fields="fieldsFiltered"
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
              :id="'store-'+data.value"
              href="#"
              class="ui-corner-all"
            >
              {{ data.value }}
            </a>
            <b-Popover
              ref="popover"
              :target="'store-'+data.value"
              triggers="hover focus"
              placement="auto"
              container="popoverContainer"
              boundary="window"
            >
              <div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front popover-content">
                <div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
                  <span
                    id="ui-id-2"
                    class="ui-dialog-title"
                  >
                    {{ data.value }}
                  </span>
                  <button
                    class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close"
                    role="button"
                    aria-disabled="false"
                    title="close"
                    @click="onClose"
                  >
                    <span class="ui-button-icon-primary ui-icon ui-icon-closethick" />
                  </button>
                </div>
                <div
                  id="b_content"
                  class="ui-dialog-content ui-widget-content"
                >
                  <div class="input-wrapper">
                    <div class="inner">
                      <label class="wrapper-label ui-widget">
                        Adresse
                      </label>
                      <div class="element-wrapper">
                        <a
                          class="nav-link"
                          :href="mapLink(data.item)"
                          title="Karte"
                        >
                          <i class="fas fa-map-marker-alt" />
                        </a>
                        <span>
                          {{ data.item.address }}
                          <br>
                          {{ data.item.city }}
                        </span>
                      </div>
                    </div>
                  </div>
                  <div
                    class="ui-padding"
                  >
                    <a
                      class="lbutton ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"
                      :href="$url('store', data.item.id)"
                      role="button"
                      aria-disabled="false"
                    >
                      <span class="ui-button-text">
                        Zur Teamseite
                      </span>
                    </a>
                  </div>
                </div>
              </div>
            </b-popover>
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
import bPopover from '@b/components/popover/popover'
import bPagination from '@b/components/pagination/pagination'
import bFormSelect from '@b/components/form-select/form-select'
import bTooltip from '@b/directives/tooltip/tooltip'
import StoreStatusIcon from './StoreStatusIcon.vue'

export default {
  components: { bTable, bPopover, bPagination, bFormSelect, StoreStatusIcon },
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
        city: {
          label: 'PLZ/Ort',
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
        },
        geo: {
          label: 'geo',
          sortable: false
        }
      },
      statusOptions: [
        { value: null, text: 'Status' },
        { value: 1, text: 'Noch kein Kontakt' },
        { value: 2, text: 'Verhandlungen laufen' },
        { value: 3, text: 'In Kooperation' },
        { value: 4, text: 'Will nicht kooperieren' },
        { value: 6, text: 'Wirft nichts weg' }
      ]
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
    },
    fieldsFiltered: function () {
      let regions = []
      let fields = {}
      this.stores.map(function (value) {
        if (!regions.includes(value['region'])) regions.push(value['region'])
      })
      for (let key in this.fields) {
        if (key === 'region' && regions.length > 1) fields[key] = this.fields[key]
        else if (key !== 'region' && key !== 'address' && key !== 'geo') fields[key] = this.fields[key]
      }
      return fields
    }
  },
  methods: {
    compare: optimizedCompare,

    clearFilter () {
      this.filterStatus = null
      this.filterText = ''
    },
    onClose () {
      this.$root.$emit('bv::hide::popover')
    },
    mapLink: function (store) {
      if (window.innerWidth <= 800 && window.innerHeight <= 600) return 'geo:0,0?q=' + store.geo
      else return '?page=map&bid=' + store.id
    }
  }
}
</script>
