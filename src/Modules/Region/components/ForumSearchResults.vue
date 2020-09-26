<template>
  <div class="bootstrap">
    <div
      v-if="isEmpty && !isLoading"
      class="dropdown-header alert alert-warning"
    >
      {{ $i18n('search.noresults') }}
    </div>

    <div
      v-if="!isEmpty"
      class="found-threads"
    >
      <div class="m-1 text-center text-muted">
        <i class="fas fa-info-circle" />
        <span> {{ $i18n('search.thread-title-only') }} </span>
      </div>

      <h3 class="dropdown-header">
        <i class="fas fa-comment" /> {{ $i18n('terminology.threads') }}
      </h3>

      <search-result-entry
        v-for="thread in filteredThreads"
        :key="thread.id"
        :href="$url('forum', groupId, subforumId, thread.id)"
        :title="thread.name"
        :teaser="getThreadDate(thread)"
        teaser-icon="far fa-clock"
      />
    </div>
  </div>
</template>

<script>
import SearchResultEntry from '@/components/Topbar/Search/SearchResultEntry'
import differenceInCalendarYears from 'date-fns/differenceInCalendarYears'
import parseISO from 'date-fns/parseISO'

function match (word, e) {
  if (e.name && e.name.toLowerCase().indexOf(word) !== -1) return true
  return e.teaser && e.teaser.toLowerCase().indexOf(word) !== -1
}

export default {
  components: {
    SearchResultEntry
  },
  props: {
    threads: {
      type: Array,
      default: () => []
    },
    groupId: {
      type: Number,
      required: true
    },
    subforumId: {
      type: Number,
      required: true
    },
    query: {
      type: String,
      default: ''
    },
    isLoading: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    filteredThreads () {
      const query = this.query.toLowerCase().trim()
      const words = query.match(/[^ ,;+.]+/g)

      // filter elements, whether all of the query words are contained somewhere in name or teaser
      const filterFunction = (e) => {
        if (!words.length) return false
        for (const word of words) {
          if (!match(word, e)) return false
        }
        return true
      }
      return this.threads.filter(filterFunction)
    },
    isEmpty () {
      return !this.filteredThreads.length
    }
  },
  methods: {
    getThreadDate (thread) {
      const lastUpdated = parseISO(thread.teaser)
      if (differenceInCalendarYears(new Date(), lastUpdated) >= 1) {
        return this.$dateFormat(lastUpdated, 'full-long')
      } else {
        return this.$dateDistanceInWords(lastUpdated) + ` (${this.$dateFormat(lastUpdated, 'full-short')})`
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.dropdown-header {
    white-space: normal;
    margin-bottom: 0;
}

.found-threads ::v-deep a {
  font-size: 0.9rem;

  // teaser == date of last thread update
  & > small {
    float: right;
    margin: 0.1rem 0;
    color: var(--gray);
  }
}
</style>
