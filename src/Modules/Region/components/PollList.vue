<template>
  <div class="container bootstrap">
    <div class="msg-inside info mb-3">
      <i class="fas fa-info-circle" />
      {{ $i18n('polls.hint') }}<br>
      {{ $i18n('polls.hint_2') }}: <a :href="$url('wiki_voting')">{{ $url('wiki_voting') }}</a>
    </div>

    <div class="card mb-3 rounded">
      <div class="card-header text-white bg-primary">
        {{ $i18n('polls.ongoing') }}
      </div>

      <div
        v-if="mayCreatePoll"
        class="p-1"
      >
        <b-link
          :href="$url('pollNew', regionId)"
          class="btn btn-sm btn-secondary btn-block"
        >
          {{ $i18n('polls.new_poll') }}
        </b-link>
      </div>

      <div
        v-for="poll in ongoingPolls"
        :key="poll.id"
      >
        <b-link :href="$url('poll', poll.id)">
          <span
            class="calendar m-1"
          >
            <span class="month">{{ formatDate(convertDate(poll.endDate.date), 'MMMM') }}</span>
            <span class="day">{{ formatDate(convertDate(poll.endDate.date), 'd') }}</span>
          </span>
          <div class="title mt-2">
            <b>{{ poll.name }}</b>
          </div>
          <div class="mt-2">
            {{ $dateFormat(convertDate(poll.startDate.date)) }} - {{ $dateFormat(convertDate(poll.endDate.date)) }}
          </div>
          <span class="clear" />
        </b-link>
      </div>
    </div>

    <div
      v-if="futurePolls.length > 0"
      class="card mb-3 rounded"
    >
      <div class="card-header text-white bg-primary">
        {{ $i18n('polls.future') }}
      </div>
      <div class="card-body">
        <ul>
          <li
            v-for="poll in futurePolls"
            :key="poll.id"
            class="mb-2"
          >
            <b-link
              :href="$url('poll', poll.id)"
            >
              <b>{{ poll.name }}</b>
              <div>{{ $i18n('poll.begins_at') }}: {{ $dateFormat(convertDate(poll.startDate.date)) }}</div>
            </b-link>
          </li>
        </ul>
      </div>
    </div>

    <div
      v-if="endedPolls.length > 0"
      class="card mb-3 rounded"
    >
      <div class="card-header text-white bg-primary">
        {{ $i18n('polls.ended') }}
      </div>
      <div class="card-body">
        <ul>
          <li
            v-for="poll in endedPolls"
            :key="poll.id"
            class="mb-2"
          >
            <b-link
              :href="$url('poll', poll.id)"
            >
              <b>{{ poll.name }}</b>
              <div>{{ $i18n('poll.ended_at') }} {{ $dateFormat(convertDate(poll.endDate.date)) }}</div>
            </b-link>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import { BLink } from 'bootstrap-vue'
import { optimizedCompare } from '@/utils'
import { isBefore, isAfter, format } from 'date-fns'
import dateFnsParseISO from 'date-fns/parseISO'

export default {
  components: { BLink },
  props: {
    regionId: {
      type: Number,
      required: true,
    },
    polls: {
      type: Array,
      default: () => [],
    },
    mayCreatePoll: {
      type: Boolean,
      default: false,
    },
  },
  data () {
    return {
      currentPage: 1,
      perPage: 20,
    }
  },
  computed: {
    ongoingPolls: function () {
      return this.polls.filter(p => !this.isPollInFuture(p) && !this.isPollInPast(p))
    },
    futurePolls: function () {
      return this.polls.filter(p => this.isPollInFuture(p))
    },
    endedPolls: function () {
      return this.polls.filter(p => this.isPollInPast(p))
    },
  },
  methods: {
    compare: optimizedCompare,
    isPollInPast (poll) {
      return isBefore(this.convertDate(poll.endDate.date), new Date())
    },
    isPollInFuture (poll) {
      return isAfter(this.convertDate(poll.startDate.date), new Date())
    },
    convertDate (date) {
      return dateFnsParseISO(date)
    },
    formatDate (date, formatStr) {
      return format(date, formatStr)
    },
  },
}
</script>

<style lang="scss" scoped>
.btn {
  width: 200px;
}
</style>
