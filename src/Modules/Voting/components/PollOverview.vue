<template>
  <!-- eslint-disable vue/max-attributes-per-line -->
  <div class="bootstrap">
    <div class="card rounded">
      <div class="card-header text-white bg-primary">
        {{ poll.name }}
      </div>
      <div class="card-body">
        <ul class="poll-properties">
          <li class="poll-date">
            <b>{{ $i18n('poll.time_period') }}:</b>
            {{ $dateFormat(startDate) }} - {{ $dateFormat(endDate) }}
            <b-badge v-if="isPollInPast" pill variant="info">
              {{ $i18n('poll.in_past') }}
            </b-badge>
            <b-badge v-else-if="isPollInFuture" pill variant="secondary">
              {{ $i18n('poll.in_future') }}
            </b-badge>
          </li>
          <li class="poll-region">
            <b>{{ $i18n(isWorkGroup ? 'terminology.group' : 'terminology.region') }}:</b> {{ regionName }}
          </li>
          <li class="poll-scope">
            <b>{{ $i18n('poll.allowed_voters') }}:</b> {{ $i18n('poll.scope_description_'+poll.scope) }}
          </li>
          <li class="poll-scope">
            <b>{{ $i18n('poll.eligible_votes') }}:</b> {{ poll.eligible_to_vote }}
          </li>
          <li class="poll-type">
            <b>{{ $i18n('poll.type') }}:</b> {{ $i18n('poll.type_description_'+poll.type) }}
          </li>
        </ul>

        <div
          v-if="userAlreadyVoted"
          class="my-1 mt-3"
        >
          <b-alert
            show
            variant="dark"
          >
            {{ $i18n('poll.already_voted') }}: {{ $dateFormat(displayedVoteDate) }}
          </b-alert>
        </div>
        <div
          v-else-if="isPollInFuture"
          class="my-1 mt-3"
        >
          <b-alert
            show
            variant="dark"
          >
            {{ $i18n('poll.may_not_yet_vote') }}
          </b-alert>
        </div>
        <div
          v-else-if="!userMayVote && !isPollInPast"
          class="mt-3"
        >
          <b-alert
            show
            variant="dark"
          >
            {{ $i18n('poll.may_not_vote') }}
          </b-alert>
        </div>

        <hr>
        <Markdown :source="poll.description" />
        <hr>

        <VoteForm
          v-if="!isPollInPast"
          :poll="poll"
          :may-vote="userMayVote"
          @vote-callback="userJustVoted"
        />

        <b-alert
          v-if="userVoteDate"
          show
          variant="dark"
        >
          {{ $i18n('poll.untraceable') }}
        </b-alert>

        <ResultsTable
          v-if="isPollInPast"
          :options="poll.options"
          :num-votes="poll.votes"
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
import Markdown from '@/components/Markdown/Markdown'
import { BAlert } from 'bootstrap-vue'

export default {
  components: { ResultsTable, VoteForm, Markdown, BAlert },
  props: {
    poll: {
      type: Object,
      required: true,
    },
    regionName: {
      type: String,
      required: true,
    },
    isWorkGroup: {
      type: Boolean,
      default: false,
    },
    mayVote: {
      type: Boolean,
      default: false,
    },
    userVoteDate: {
      type: Object,
      default: null,
    },
  },
  data () {
    return {
      userMayVote: this.mayVote,
      userAlreadyVoted: this.userVoteDate !== null,
      displayedVoteDate: this.userVoteDate ? dateFnsParseISO(this.userVoteDate.date) : new Date(),
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
    },
  },
  methods: {
    userJustVoted () {
      this.userAlreadyVoted = true
      this.userMayVote = false
    },
  },
}
</script>

<style lang="scss" scoped>
.prestyled {
  white-space: pre-line;
}

.poll-properties {
  font-size: 0.875rem;

  & > li {
    margin-bottom: 0.25rem;
  }
}

.card-body {
  hr {
    // counter the .card definition of padding: 6px 8px;
    margin-left: -8px;
    margin-right: -8px;
  }

  ::v-deep label {
    max-width: 100%;
  }
}
</style>
