<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <div class="card-header text-white bg-primary">
        Alle Betriebe aus dem Bezirk {{ regionName }} (<span v-if="stores.length !== storesFiltered.length">
          {{ storesFiltered.length }} von
        </span>{{ stores.length }})
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
                <div id="myContainer">
                <div>
                  <a
                    :id="'popover-'+data.value"
                    href="#"
                    class="ui-corner-all"
                    >
                    {{ data.value }}
                    <!--:href="$url('store', data.item.id)"-->
                  </a>
                </div>
                <!--popover title and content render container -->
                <b-popover
                  :target="'popover-'+data.value"
                  triggers="hover focus"
                  placement="auto"
                  container="myContainer"
                  ref="popover"

                >
                  <template slot="title">
                    <div class="head ui-widget-header">
                      {{ data.value }}
                      <b-button @click="onClose(this)" class="close" aria-labe="Close">
                      <span class="d-inline-block" aria-hidden="true">&times;</span>
                    </b-button>
                    </div>
                  </template>


                  <div class="ui-widget ui-widget-content corner-bottom margin-bottom ui-padding">
                    <div class="input-wrapper" id="input-1">
                    <label class="wrapper-label ui-widget" for="input-1">Adresse</label>
                      <div class="element-wrapper">
                    {{ data.item.address }}
                      <br/>
                        {{ data.item.plz }} {{ data.item.city }}
                        <a
                        :href="'?page=map&bid='+data.item.id"
                        class="nav-link"
                        >
                        <i class="fas fa-map-marker-alt" />
                        <span v-if="!loggedIn || !hasFsRole">
                  Karte
                </span>
                      </a>
                      </div>
                  </div>
                    <div class="buttonrow">
                      <a
                        class="lbutton ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"
                        :href="$url('store', data.item.id)"
                        role="button"
                        aria-disabled="false">
                        <span class="ui-button-text">Zur Teamseite</span>
                      </a>
                    </div>
                  </div>

                </b-popover>



                </div>
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
import bTable from '@b/components/table/table'
import bButton from '@b/components/button/button'
import bPagination from '@b/components/pagination/pagination'
import bFormSelect from '@b/components/form-select/form-select'
import bTooltip from '@b/directives/tooltip/tooltip'
import StoreStatusIcon from './StoreStatusIcon.vue'
import bPopover from '@b/components/popover/popover'


const noLocale = /^[\w-.\s,]*$/

export default {
  components: { bTable, bButton, bPagination, bFormSelect, StoreStatusIcon, bPopover },
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
      popoverShow: false,
      fields: {
        status: {
          label: 'Status',
          tdClass: 'status',
          sortable: true
        },
        name: {
          label: 'Name',
          sortable: true,
        },
        address: {
          label: 'Anschrift',
          sortable: true
        },
        zipCode: {
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
      compare (a, b, key) {
        const elemA = a[key]
        const elemB = b[key]
        if (typeof elemA === 'number' || (noLocale.test(elemA) && noLocale.test(elemB))) {
          if (typeof elemA === 'string') {
            const a = elemA.toLowerCase()
            const b = elemB.toLowerCase()
            return (a > b ? 1 : (a === b ? 0 : -1))
          }
          return (elemA > elemB ? 1 : (elemA === elemB ? 0 : -1))
        } else {
          return elemA.localeCompare(elemB)
        }
      }
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
            store.region.toLowerCase().indexOf(filterText) !== -1 ||
            store.city.toLowerCase().indexOf(filterText) !== -1
          ))
        )
      })
    },

    fieldsFiltered: function() {
      var regions=[]
      this.stores.map(function(value, key) {
        console.log(regions.includes(value['region']))
        if (!regions.includes(value['region'])) {
          regions.push(value['region'])
        }
      })
      const filterText = regions.length < 2 ? "region" : null

      return Object.keys(this.fields).filter(f =>
        f !== "zipCode" && f !== "address" && f !== filterText
      )
    }


  },
  methods: {
    clearFilter () {
      this.filterStatus = null
      this.filterText = ''
    },
    onClose(popover) {
      this.popoverShow = false;
    },
    onOk () {

    },
    onShow() {
      this.input1 = '';
      this.input2 = '';
      this.input1state = null;
      this.input2state = null;
      this.input1Return = '';
      this.input2Return = '';
    },
    onShown () {
      /* Called just after the popover has been shown */
      /* Transfer focus to the first input */
      this.focusRef(this.$refs.input1);
    },
    onHidden () {
      /* Called just after the popover has finished hiding */
      /* Bring focus back to the button */
      this.focusRef(this.$refs.button);
    },
    focusRef (ref) {
      /* Some references may be a component, functional component, or plain element */
      /* This handles that check before focusing, assuming a focus() method exists */
      /* We do this in a double nextTick to ensure components have updated & popover positioned first */
      this.$nextTick(() => {
        this.$nextTick(() => { (ref.$el || ref).focus() });
      });
    }

  }
}
</script>
