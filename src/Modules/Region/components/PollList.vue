<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <div
        class="card-header text-white bg-primary"
      >
        {{ $i18n('terminology.polls') }}
        <span>
          ({{ polls.length }})
        </span>
      </div>

      <b-table
        :fields="fields"
        :items="polls"
        :current-page="currentPage"
        :per-page="perPage"
        :sort-compare="compare"
        small
        hover
        responsive
        class="foto-table"
      >
        <template v-slot:cell(title)="row">
          <a
            :href="$url('poll', row.item.id)"
          >
            {{ row.item.name }}
          </a>
        </template>
        <template v-slot:cell(startDate)="row">
          <a
            :href="$url('poll', row.item.id)"
          >
            {{ row.item.startDate.date }}
          </a>
        </template>
        <template v-slot:cell(endDate)="row">
          <a
            :href="$url('poll', row.item.id)"
          >
            {{ row.item.endDate.date }}
          </a>
        </template>
      </b-table>
      <div class="float-right p-1 pr-3">
        <b-pagination
          v-model="currentPage"
          :total-rows="polls.length"
          :per-page="perPage"
          class="my-0"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { BTable, BPagination, VBTooltip } from 'bootstrap-vue'
import { dateFormat, optimizedCompare } from '@/utils'
import isPast from 'date-fns/isPast'

export default {
  components: { BTable, BPagination },
  directives: { VBTooltip, dateFormat },
  props: {
    regionId: {
      type: Number,
      required: true
    },
    polls: {
      type: Array,
      default: () => []
    }
  },
  data () {
    return {
      currentPage: 1,
      perPage: 20,
      fields: [
        {
          key: 'title',
          label: this.$i18n('polls.title'),
          sortable: true,
          class: 'align-middle'
        }, {
          key: 'startDate',
          sortable: true,
          label: this.$i18n('polls.start_date'),
          class: 'align-middle'
        }, {
          key: 'endDate',
          sortable: true,
          label: this.$i18n('polls.end_date'),
          class: 'align-middle'
        }
      ]
    }
  },
  methods: {
    compare: optimizedCompare,
    isActivePoll (poll) {
      return isPast(poll.startDate) && !isPast(poll.endDate)
    }
  }
}
</script>

<style lang="scss" scoped>
</style>
