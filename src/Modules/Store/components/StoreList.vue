<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <div class="card-header text-white bg-primary">
        {{ $i18n('store.allStoresOfRegion') }} {{ regionName }}
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
              type="button"
              class="btn btn-sm"
              :title="$i18n('storelist.emptyfilters')"
              @click="clearFilter"
            >
              <i class="fas fa-times" />
            </button>
          </div>
          <div
            v-if="showCreateStore"
            :regionId="regionId"
            class="col"
          >
            <a
              :href="$url('storeAdd', regionId)"
              class="btn btn-sm btn-secondary btn-block"
            >
              {{ $i18n('store.addNewStoresButton') }}
            </a>
          </div>
        </div>
        <b-table
          id="store-list"
          :fields="fieldsFiltered"
          :current-page="currentPage"
          :per-page="perPage"
          :sort-by.sync="sortBy"
          :sort-desc.sync="sortDesc"
          :items="storesFiltered"
          small
          hover
          responsive
        >
          <template
            v-slot:cell(status)="row"
            :v-if="isMobile"
          >
            <div class="text-center">
              <StoreStatusIcon :status="row.value" />
            </div>
          </template>
          <template
            v-slot:cell(name)="row"
          >
            <a
              :href="$url('store', row.item.id)"
              class="ui-corner-all"
            >
              {{ row.value }}
            </a>
          </template>
          <template
            v-slot:cell(actions)="row"
          >
            <b-button
              size="sm"
              @click.stop="row.toggleDetails"
            >
              {{ row.detailsShowing ? 'x' : 'Details' }}
            </b-button>
          </template>
          <template
            v-slot:row-details="row"
          >
            <b-card>
              <div class="details">
                <p>
                  <strong>{{ $i18n('storelist.addressdata') }}</strong><br>
                  {{ row.item.address }} <a
                    :href="mapLink(row.item)"
                    class="nav-link details-nav"
                    :title="$i18n('storelist.map')"
                  >
                    <i class="fas fa-map-marker-alt" />
                  </a><br> {{ row.item.zipcode }} {{ row.item.city }}
                </p>
                <p><strong>{{ $i18n('storelist.entered') }}</strong> {{ row.item.added }}</p>
              </div>
            </b-card>
          </template>
        </b-table>
        <div class="float-right p-1 pr-3">
          <b-pagination
            v-model="currentPage"
            :total-rows="storesFiltered.length"
            :per-page="perPage"
            aria-controls="store-list"
            class="my-0"
          />
        </div>
      </div>
      <div
        v-else
        class="card-body d-flex justify-content-center"
      >
        {{ $i18n('store.noStores') }}
        <div
          v-if="showCreateStore"
          :regionId="regionId"
          class="col"
        >
          <a
            :href="$url('storeAdd', regionId)"
            class="btn btn-sm btn-secondary btn-block"
          >
            {{ $i18n('store.addNewStoresButton') }}
          </a>
        </div>
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
import i18n from '@/i18n'

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
    },
    regionId: {
      type: Number,
      default: 0
    },
    showCreateStore: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      sortBy: 'added',
      sortDesc: true,
      currentPage: 1,
      perPage: 20,
      filterText: '',
      filterStatus: null,
      fields: [
        {
          key: 'status',
          label: i18n('storelist.status'),
          tdClass: 'status',
          sortable: true
        },
        {
          key: 'name',
          label: i18n('storelist.name'),
          sortable: true
        },
        {
          key: 'address',
          label: i18n('storelist.address'),
          sortable: true
        },
        {
          key: 'zipcode',
          label: i18n('storelist.zipcode'),
          sortable: true
        },
        {
          key: 'city',
          label: i18n('storelist.city'),
          sortable: true
        },
        {
          key: 'added',
          label: i18n('storelist.added'),
          sortable: true
        },
        {
          key: 'region',
          label: i18n('storelist.region'),
          sortable: true
        },
        {
          key: 'geo',
          label: i18n('storelist.geo'),
          sortable: false
        },
        {
          key: 'actions',
          label: '',
          sortable: false
        }
      ],
      statusOptions: [
        { value: null, text: 'Status' },
        { value: 1, text: i18n('storelist.nocontact') },
        { value: 2, text: i18n('storelist.inprogress') },
        { value: 3, text: i18n('storelist.cooperating') },
        { value: 4, text: i18n('storelist.notcooperating') },
        { value: 6, text: i18n('storelist.nowaste') }
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
      const fields = []
      this.stores.map(function (value) {
        if (!regions.includes(value.region)) regions.push(value.region)
      })
      const displayableFields = (window.innerWidth > 800 && window.innerHeight > 600)
        ? ['region', 'geo', 'actions']
        : ['region', 'geo', 'address', 'added', 'zipcode']

      this.fields.forEach(field => {
        if ((field.key === 'region' && regions.length > 1) ||
          !displayableFields.includes(field.key)) {
          fields.push(field)
        }
      })

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
