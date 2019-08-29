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
            <label>
              <input
                v-model="filterText"
                type="text"
                class="form-control form-control-sm"
                placeholder="Name/Adresse"
              >
            </label>
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
              @click="clearFilter"
              type="button"
              class="btn btn-sm"
              title="Filter leeren"
            >
              <i class="fas fa-times" />
            </button>
          </div>
        </div>

        <b-table
          :fields="fieldsFiltered"
          :current-page="currentPage"
          :per-page="perPage"
          :sort-by.sync="sortBy"
          :items="storesFiltered"
          small
          hover
          responsive
        >
          <template
            slot="status"
            slot-scope="row"
            :v-if="isMobile"
          >
            <div class="text-center">
              <StoreStatusIcon :status="row.value" />
            </div>
          </template>
          <template
            slot="name"
            slot-scope="row"
          >
            <a
              :href="$url('store', row.item.id)"
              class="ui-corner-all"
            >
              {{ row.value }}
            </a>
          </template>
          <template
            slot="actions"
            slot-scope="row"
          >
            <b-button
              @click.stop="row.toggleDetails"
              size="sm"
            >
              {{ row.detailsShowing ? 'x' : 'Details' }}
            </b-button>
          </template>
          <template
            slot="row-details"
            slot-scope="row"
          >
            <b-card>
              <div class="details">
                <p>
                  <strong>Anschrift:</strong><br>
                  {{ row.item.address }} <a
                    :href="mapLink(row.item)"
                    class="nav-link details-nav"
                    title="Karte"
                  >
                    <i class="fas fa-map-marker-alt" />
                  </a><br> {{ row.item.zipcode }} {{ row.item.city }}
                </p>
                <p><strong>Eingetragen:</strong> {{ row.item.added }}</p>
              </div>
            </b-card>
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

import {
  BTable,
  BPagination,
  BFormSelect,
  VBTooltip,
  BButton,
  BCard
} from 'bootstrap-vue'

import StoreStatusIcon from './StoreStatusIcon.vue'

export default {
  components: { BCard, BTable, BButton, BPagination, BFormSelect, StoreStatusIcon },
  directives: { VBTooltip },
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
      sortBy: 'name',
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
          label: 'Straße',
          sortable: true
        },
        zipcode: {
          label: 'PLZ',
          sortable: true
        },
        city: {
          label: 'Ort',
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
        },
        actions: {
          label: '',
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
      const filterText = this.filterText ? this.filterText.toLowerCase() : null
      return Array.from(this.stores.filter((store) => {
        return (
          (!this.filterStatus || store.status === this.filterStatus) &&
          (!filterText || (
            store.name.toLowerCase().indexOf(filterText) !== -1 ||
            store.address.toLowerCase().indexOf(filterText) !== -1 ||
            store.region.toLowerCase().indexOf(filterText) !== -1 ||
            store.city.toLowerCase().indexOf(filterText) !== -1 ||
            store.zipcode.toLowerCase().indexOf(filterText) !== -1
          ))
        )
      }))
    },
    fieldsFiltered: function () {
      const regions = []
      const fields = {}
      this.stores.map(function (value) {
        if (!regions.includes(value.region)) regions.push(value.region)
      })
      if (window.innerWidth > 800 && window.innerHeight > 600) {
        for (const key in this.fields) {
          if (key === 'region' && regions.length > 1) fields[key] = this.fields[key]
          else if (key !== 'region' && key !== 'geo' && key !== 'actions') fields[key] = this.fields[key]
        }
      } else {
        for (const key in this.fields) {
          if (key === 'region' && regions.length > 1) fields[key] = this.fields[key]
          else if (key !== 'region' && key !== 'geo' && key !== 'address' && key !== 'added' && key !== 'zipcode') fields[key] = this.fields[key]
        }
      }
      return fields
    }
  },
  methods: {
    clearFilter () {
      this.filterStatus = null
      this.filterText = ''
    },
    mapLink: function (store) {
      return 'geo:0,0?q=' + store.geo
    }
  }
}
</script>
<style>
  .details-nav {
    float:right;
    font-size: 2em;
  }
</style>
