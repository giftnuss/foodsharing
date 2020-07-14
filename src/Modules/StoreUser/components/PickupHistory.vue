<template>
  <!-- eslint-disable vue/max-attributes-per-line -->
  <div class="store-pickup-history">
    <div
      v-if="storeId != null"
      class="head ui-widget-header ui-corner-top"
      @click.prevent="toggleDisplay"
    >
      {{ $i18n('pickup.history.title') }}
      <a
        class="float-right pl-2 pr-1"
        href="#"
        @click.prevent.stop="toggleDisplay"
      >
        <i :class="['fas fa-fw', `fa-chevron-${display ? 'down' : 'left'}`]" />
      </a>
    </div>
    <div class="corner-bottom margin-bottom bootstrap pickup-history">
      <div
        :class="{'p-1': true, 'd-none': !display}"
      >
        <b-form id="pickup-date-form" class="p-1" inline>
          <label class="sr-only" for="datepicker-from">From date:</label>
          <b-form-datepicker
            id="datepicker-from"
            v-model="fromDate"
            v-bind="calendarLabels"
            :value-as-date="true"
            :date-format-options="dateFormatOptions"
            selected-variant="secondary"
            :max="maxDateFrom"
            :min="minDateFrom"
            :required="true"
            form="pickup-date-form"
            :hide-header="true"
            :start-weekday="1"
            :locale="$i18n('calendar.locale')"
            no-highlight-today
          />

          <hr class="w-auto date-separator">

          <label class="sr-only" for="datepicker-from">To date:</label>
          <b-form-datepicker
            id="datepicker-to"
            v-model="toDate"
            v-bind="calendarLabels"
            :value-as-date="true"
            :date-format-options="dateFormatOptions"
            selected-variant="secondary"
            :max="maxDateTo"
            :min="minDateTo"
            :hide-header="true"
            :start-weekday="1"
            :locale="$i18n('calendar.locale')"
            no-highlight-today
          />
        </b-form>
        <div class="p-1 pickup-search-button">
          <b-button
            variant="secondary"
            size="sm"
            class="d-block mx-auto"
            :class="{'disabled': !searchable}"
            @click.prevent="searchHistory"
          >
            <i class="fas fa-fw fa-search" />
            {{ $i18n('pickup.history.search') }}
          </b-button>
        </div>

        <div class="p-1 pickup-table">
          <Pickup
            v-for="pickupDate in pickupList"
            :key="pickupDate[0].date_ts"
            v-bind="pickupDate"
            :date="pickupDate[0].date"
            :store-id="storeId"
            :occupied-slots="pickupDate"
            :show-relative-date="true"
            class="pickup-block"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import parseISO from 'date-fns/parseISO'
import startOfDay from 'date-fns/startOfDay'
import endOfDay from 'date-fns/endOfDay'
import min from 'date-fns/min'
import max from 'date-fns/max'
import sub from 'date-fns/sub'
import { listPickupHistory } from '@/api/stores'
import i18n from '@/i18n'
import { pulseError } from '@/script'
import Pickup from './Pickup'

const calendarLabels = {
  labelPrevYear: i18n('calendar.labelPrevYear'),
  labelPrevMonth: i18n('calendar.labelPrevMonth'),
  labelCurrentMonth: i18n('calendar.labelCurrentMonth'),
  labelNextMonth: i18n('calendar.labelNextMonth'),
  labelNextYear: i18n('calendar.labelNextYear'),
  labelToday: i18n('calendar.labelToday'),
  labelSelected: i18n('calendar.labelSelected'),
  labelNoDateSelected: i18n('calendar.labelNoDateSelected'),
  labelCalendar: i18n('calendar.labelCalendar'),
  labelNav: i18n('calendar.labelNav'),
  labelHelp: i18n('calendar.labelHelp')
}

export default {
  components: { Pickup },
  props: {
    storeId: { type: Number, required: true },
    coopStart: { type: String, default: '' }
  },
  data () {
    const maxDate = new Date()
    const minDate = sub(new Date(), { years: 10, months: 1, days: 1 })

    const dateFormatOptions = {
      year: 'numeric',
      month: '2-digit',
      day: 'numeric',
      weekday: 'short'
    }

    return {
      display: false,
      isLoading: false,
      fromDate: null,
      toDate: maxDate,
      dateFormatOptions,
      maxDateTo: maxDate,
      minDateFrom: this.coopStart ? max([minDate, parseISO(this.coopStart)]) : minDate,
      pickupList: [],
      calendarLabels: calendarLabels
    }
  },
  computed: {
    minDateTo () {
      return this.fromDate || this.minDateFrom
    },
    maxDateFrom () {
      return this.toDate || this.maxDateTo
    },
    searchable () {
      return !this.isLoading && this.fromDate && this.toDate
    }
  },
  methods: {
    toggleDisplay () {
      this.display = !this.display
    },
    async searchHistory () {
      if (!this.searchable) {
        return
      }
      this.isLoading = true
      try {
        this.pickupList = await listPickupHistory(
          this.storeId,
          startOfDay(this.fromDate),
          min([new Date(), endOfDay(this.toDate)])
        )
      } catch (e) {
        pulseError(i18n('error_unexpected') + e)
      }
      this.isLoading = false
    },
    when (dt) {
      return parseISO(dt)
    }
  }
}
</script>

<style lang="scss" scoped>
.bootstrap.pickup-history {
  background: var(--white);

  ::v-deep .form-inline .form-control.b-calendar-grid {
    width: 100%;
  }

  .date-separator::after {
    content: '->';
    visibility: hidden;
  }
}
</style>
