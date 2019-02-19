<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <div
        v-if="!isWorkGroup"
        class="card-header text-white bg-primary"
      >
        {{ $i18n('memberlist.header_for_district', {bezirk: regionName}) }}
        <span>
          {{ $i18n('memberlist.some_in_all', {some: membersFiltered.length, all: members.length}) }}
        </span>
      </div>
      <div
        v-if="isWorkGroup"
        class="card-header text-white bg-primary"
      >
        {{ $i18n('memberlist.header_for_workgroup', {bezirk: regionName}) }}
        <span>
          {{ $i18n('memberlist.some_in_all', {some: membersFiltered.length, all: members.length}) }}
        </span>
      </div>

      <div
        v-if="members.length"
        class="card-body p-0"
      >
        <div class="form-row p-1 ">
          <div class="col-2 text-center">
            <label class=" col-form-label col-form-label-sm foo">
              {{ $i18n('list.filter_for') }}
            </label>
          </div>
          <div class="col-8">
            <input
              v-model="filterText"
              type="text"
              class="form-control form-control-sm"
              placeholder="Name"
            >
          </div>
          <div class="col">
            <button
              v-b-tooltip.hover
              type="button"
              class="btn btn-sm"
              :title="$i18n('button.clear_filter')"
              @click="clearFilter"
            >
              <i class="fas fa-times" />
            </button>
          </div>
        </div>
      </div>

      <b-table
        :fields="fields"
        :items="membersFiltered"
        :current-page="currentPage"
        :per-page="perPage"
        :sort-compare="compare"
        small
        hover
        responsive
        class="foto-table"
      >
        <template
          slot="imageUrl"
          slot-scope="data"
        >
          <div>
            <img
              :src="data.value"
              :alt="$i18n('terminology.profile_picture')"
            />
          </div>
        </template>
        <template
          slot="user.name"
          slot-scope="{ item: { user } }"
        >
          <a
            :href="$url('profile', user.id)"
          >
            {{ user.name }}
          </a>
        </template>
      </b-table>
      <div class="float-right p-1 pr-3">
        <b-pagination
          v-model="currentPage"
          :total-rows="membersFiltered.length"
          :per-page="perPage"
          class="my-0"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { optimizedCompare } from '@/utils'
import bTable from '@b/components/table/table'
import bPagination from '@b/components/pagination/pagination'
import bTooltip from '@b/directives/tooltip/tooltip'

export default {
  components: { bTable, bPagination },
  directives: { bTooltip },
  props: {
    regionName: {
      type: String,
      default: ''
    },
    members: {
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
      currentPage: 1,
      perPage: 20,
      filterText: '',
      fields: {
        imageUrl: {
          label: '',
          sortable: false,
          class: 'foto-column'
        },
        'user.name': {
          label: this.$i18n('group.name'),
          sortable: false,
          class: 'align-middle'
        }
      }
    }
  },
  computed: {
    membersFiltered () {
      if (!this.filterText.trim()) {
        return this.members
      }
      let filterText = this.filterText ? this.filterText.toLowerCase() : null
      return this.members.filter((member) => {
        return (
          !filterText || (member.user.name.toLowerCase().indexOf(filterText) !== -1
          )
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

<style lang="scss" scoped>
.foto-table /deep/ .foto-column {
  width: 60px;
}
</style>
