<template>
  <div class="bootstrap">
    <div class="card rounded">
      <div class="card-header text-white bg-primary">
        {{ poll.name }}
      </div>
      <div class="card-body">
        <div class="prestyled mb-3">
          {{ poll.description }}
        </div>
        <div>
          <b>{{ $i18n('poll.time_period') }}:</b> {{ $dateFormat(startDate) }} - {{ $dateFormat(endDate) }}
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
        <div
          v-if="userVoteDate !== null"
          class="my-1 mt-3"
        >
          {{ $i18n('poll.already_voted') }}: {{ $dateFormat(parseDate(userVoteDate.date)) }}
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
          :num-votes="poll.votes"
          class="mt-5"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { isBefore, isAfter } from 'date-fns'
import dateFnsParseISO from 'date-fns/parseISO'
import VoteForm from './VoteForm'
import ResultsTable from './ResultsTable'

export default {
  components: { ResultsTable, VoteForm },
  props: {
    poll: {
      type: Object,
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
    },
    userVoteDate: {
      type: Object,
      default: null
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
    },
    parseDate (date) {
      return dateFnsParseISO(date)
    }
  }
}
</script>

<style lang="scss">
.prestyled {
  white-space: pre-line;
}
</style>
