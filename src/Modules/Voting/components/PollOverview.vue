<template>
  <div class="bootstrap">
    <div class="card rounded">
      <div class="card-header text-white bg-primary">
        {{ poll.name }}
      </div>
      <div class="card-body">
        <form class="my-1 mb-3">
          {{ poll.description }}
        </form>
        <div>
          <b>{{ $i18n('poll.time_period') }}:</b> {{ startDate | dateFormat('full-short') }} - {{ endDate | dateFormat('full-short') }}
          <span v-if="isPollInPast">
            ({{ $i18n('poll.in_past') }})
          </span>
          <span v-else-if="isPollInFuture">
            ({{ $i18n('poll.in_future') }})
          </span>
        </div>
        <div class="my-1">
          <b>{{ $i18n(isWorkGroup ? 'terminology.group' : 'terminology.region') }}:</b> {{ regionName }}
        </div>
        <div class="my-1">
          <b>{{ $i18n('poll.allowed_voters') }}:</b> {{ $i18n('poll.scope_description_'+poll.scope) }}
        </div>
        <div class="my-1">
          <b>{{ $i18n('poll.type') }}:</b> {{ $i18n('poll.type_description_'+poll.type) }}
        </div>

        <VoteForm
          v-if="mayVote"
          :poll="poll"
          :may-vote="mayVote"
          class="mt-5"
          @disableVoteForm="disableVoteForm"
        />

        <ResultsTable
          v-if="isPollInPast"
          :options="poll.options"
          :num-values="numValues"
          :num-votes="poll.votes"
          class="mt-5"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { dateFormat } from '@/utils'
import { isBefore, isAfter } from 'date-fns'
import dateFnsParseISO from 'date-fns/parseISO'
import VoteForm from './VoteForm'
import ResultsTable from './ResultsTable'

export default {
  components: { ResultsTable, VoteForm },
  directives: { dateFormat },
  props: {
    poll: {
      type: Object,
      required: true
    },
    numValues: {
      type: Number,
      required: true
    },
    regionName: {
      type: String,
      required: true
    },
    isWorkGroup: {
      type: Boolean,
      default: false
    },
    mayVote: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    startDate () {
      return dateFnsParseISO(this.poll.startDate.date)
    },
    endDate () {
      return dateFnsParseISO(this.poll.endDate.date)
    },
    isPollInPast () {
      return isBefore(this.endDate, new Date())
    },
    isPollInFuture () {
      return isAfter(this.startDate, new Date())
    }
  },
  methods: {
    disableVoteForm () {
      this.mayVote = false
    }
  }
}
</script>
