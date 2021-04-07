<!-- eslint-disable vue/max-attributes-per-line -->
<template>
  <div class="event-panel bootstrap margin-bottom">
    <b-card>
      <b-media no-body class="d-flex w-100">
        <b-media-aside
          v-b-tooltip.hover="dateTooltip"
          class="mr-2 flex-column"
        >
          <CalendarDate :date-object="startDate" />
        </b-media-aside>

        <b-media-body class="ml-1 w-100">
          <div class="d-flex flex-wrap-reverse flex-sm-nowrap w-100 justify-content-between">
            <a :href="$url('event', eventId)" class="event-link">
              <h6 class="my-0 mr-1">
                {{ title }}
                <b-button
                  v-if="mayEdit"
                  v-b-tooltip="$i18n('events.edit')"
                  :href="$url('eventEdit', eventId)"
                  size="sm"
                  variant="outline-secondary ml-2"
                >
                  <i class="fas fa-fw fa-pencil-alt" />
                </b-button>
              </h6>
            </a>
            <div v-if="regionName" class="flex-md-shrink-0 text-muted">
              {{ regionName }}
            </div>
          </div>

          <div
            v-b-tooltip.hover="$i18n('events.duration', { duration: displayedDuration })"
            class="my-1 d-inline-block event-date"
          >
            <i class="far fa-fw fa-clock" />
            {{ $i18n('events.span', { from: displayedStart, until: displayedEnd }) }}
          </div>
          <br>

          <b-button-group size="sm">
            <b-button
              :variant="statusVariant(1)"
              @click="acceptInvitation(eventId); currentStatus = 1"
            >
              <i class="fas fa-fw fa-calendar-check" />
              {{ $i18n('events.button.yes') }}
            </b-button>
            <b-button
              :variant="statusVariant(2)"
              @click="maybeInvitation(eventId); currentStatus = 2"
            >
              <i class="fas fa-fw fa-question-circle" />
              <span class="d-none d-sm-inline">
                {{ $i18n('events.button.maybe') }}
              </span>
            </b-button>
            <b-button
              :variant="statusVariant(3)"
              @click="declineInvitation(eventId)"
            >
              <!-- TODO faded UI after clicking (don't remove, to allow correcting mis-clicks) -->
              <i class="fas fa-fw fa-calendar-times" />
              <span class="d-none d-sm-inline">
                {{ $i18n('events.button.no') }}
              </span>
            </b-button>
          </b-button-group>
        </b-media-body>
      </b-media>
    </b-card>
  </div>
</template>

<script>
import dateFnsLocaleDE from 'date-fns/locale/de'
import isSameDay from 'date-fns/isSameDay'
import formatDate from 'date-fns/format'
import formatDistanceStrict from 'date-fns/formatDistanceStrict'
import parseISO from 'date-fns/parseISO'
import { acceptInvitation, declineInvitation, maybeInvitation } from '@/api/events'
import CalendarDate from '@/components/CalendarDate'

export default {
  components: { CalendarDate },
  props: {
    eventId: { type: Number, required: true },
    regionName: { type: String, default: '' },
    start: { type: String, required: true },
    end: { type: String, required: true },
    title: { type: String, default: '' },
    mayEdit: { type: Boolean, default: false },
    status: { type: Number, default: 0 },
  },
  data () {
    return {
      startDate: parseISO(this.start),
      endDate: parseISO(this.end),
      currentStatus: this.status,
    }
  },
  computed: {
    displayedDay () {
      return formatDate(this.startDate, 'dd')
    },
    displayedMonth () {
      return this.$i18n('month.' + formatDate(this.startDate, 'M'))
    },
    dateTooltip () {
      return formatDate(this.startDate, 'dd.MM.yyyy HH:mm') + ' (' + this.$dateDistanceInWords(this.startDate) + ')'
    },
    displayedStart () {
      return formatDate(this.startDate, 'HH:mm')
    },
    displayedDuration () {
      return formatDistanceStrict(this.endDate, this.startDate, { locale: dateFnsLocaleDE })
    },
    displayedEnd () {
      if (isSameDay(this.endDate, this.startDate)) {
        return formatDate(this.endDate, 'HH:mm')
      } else {
        return formatDate(this.endDate, '[d.MM.] HH:mm')
      }
    },
  },
  methods: {
    formatDate,
    acceptInvitation,
    maybeInvitation,
    declineInvitation,
    statusVariant: function (s) {
      if (s === this.currentStatus) {
        return 'secondary'
      } else {
        return 'outline-primary'
      }
    },
    edit: function () {},
  },
}
</script>

<style lang="scss" scoped>
// pure-grid is doing very weird things on the dashboard without this:
.event-panel div.btn-group > .btn {
  white-space: initial;
}

.event-link {
  color: initial;
}

.event-date {
  font-size: 0.8rem;
}
</style>
