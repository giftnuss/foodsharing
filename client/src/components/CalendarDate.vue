<template>
  <!-- eslint-disable-next-line vue/max-attributes-per-line -->
  <div class="datebox corner-all" :class="classes">
    <div class="px-1 month">
      {{ displayedMonth }}
    </div>
    <div
      v-if="!isEventToday && !isEventTomorrow"
      class="px-1 day"
    >
      {{ displayedWeekday }} {{ displayedDay }}
    </div>
    <div
      v-else-if="isEventToday"
      class="px-1 day"
      style="font-size: 100%"
    >
      {{ today }}
    </div>
    <div
      v-else-if="isEventTomorrow"
      class="px-1 day"
      style="font-size: 100%"
    >
      {{ tomorrow }}
    </div>
  </div>
</template>

<script>
import formatDate from 'date-fns/format'
import isToday from 'date-fns/isToday'
import isTomorrow from 'date-fns/isTomorrow'

export default {
  props: {
    dateObject: { type: Date, required: true },
    classes: { type: String, default: '' },
  },
  computed: {
    displayedDay () {
      return formatDate(this.dateObject, 'dd')
    },
    displayedWeekday () {
      return this.$i18n('date_short.' + formatDate(this.dateObject, 'EEEE'))
    },
    displayedMonth () {
      return this.$i18n('month.' + formatDate(this.dateObject, 'M'))
    },
    isEventToday () {
      return isToday(this.dateObject)
    },
    isEventTomorrow () {
      return isTomorrow(this.dateObject)
    },
    today () {
      return this.$i18n('date.Today')
    },
    tomorrow () {
      return this.$i18n('date.-- Tomorrow')
    },
  },
}
</script>

<style lang="scss" scoped>
.datebox {
  --calendar-highlight-bg: #ff8746; // new orange
  --calendar-highlight-text: #45a045; // modified kale
  --calendar-font-size: 1rem;
  --calendar-line-height: 1.2;
  --calendar-border-radius: 6px;

  text-align: center;

  .month {
    min-width: calc(5 * var(--calendar-font-size));
    border-top-left-radius: var(--calendar-border-radius);
    border-top-right-radius: var(--calendar-border-radius);
    font-size: var(--calendar-font-size);
    letter-spacing: -.5px;
    line-height: var(--calendar-line-height);
    font-weight: bold;
    background-color: var(--calendar-highlight-bg);
    color: var(--white);
    text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.75);
  }

  .day {
    border: 2px solid var(--border);
    border-radius: var(--calendar-border-radius);
    border-top: 0;
    border-top-left-radius: 0;
    border-top-right-radius: 0;
    color: var(--calendar-highlight-text);
    font-family: 'Alfa Slab One', serif;
    font-size: calc(1.57 * var(--calendar-font-size));
    line-height: var(--calendar-line-height);

    // letter-spacing has alignment problems
    &::first-letter {
      margin-right: calc(0.1 * var(--calendar-font-size));
    }
  }
}
</style>
