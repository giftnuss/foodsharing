<template>
  <div class="bootstrap">
    <div
      v-if="isEmpty && !isLoading"
      class="dropdown-header alert alert-warning"
    >
      {{ $i18n('search.noresults') }}
    </div>

    <div v-if="filtered.threads.length">
      <h3 class="dropdown-header">
        <i class="fas fa-comment" /> {{ $i18n('terminology.themes') }}
      </h3>
      <search-result-entry
        v-for="thread in filtered.threads"
        :key="thread.id"
        :href="$url('forum', groupId, subforumId, thread.id)"
        :title="thread.name"
      />
    </div>
  </div>
</template>

<script>

import SearchResultEntry from '@/components/Topbar/Search/SearchResultEntry'
import { VBTooltip } from 'bootstrap-vue'

function match (word, e) {
  if (e.name && e.name.toLowerCase().indexOf(word) !== -1) return true
  return e.teaser && e.teaser.toLowerCase().indexOf(word) !== -1
}

export default {
  components: {
    SearchResultEntry
  },
  directives: { VBTooltip },
  props: {
    threads: {
      type: Array,
      default: () => []
    },
    groupId: {
      type: Number,
      default: -1
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
    filtered () {
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
      return {
        threads: this.threads.filter(filterFunction)
      }
    },
    isEmpty () {
      return !this.filtered.threads.length
    }
  }
}
</script>

<style lang="scss" scoped>
.dropdown-header {
    white-space: normal;
    margin-bottom: 0;
}
</style>
