<!-- eslint-disable vue/max-attributes-per-line -->
<template>
  <div>
    <div class="ui-widget-content ui-corner-all margin-bottom ui-padding">
      <a
        class="button"
        :href="$url('forum', groupId, subforumId) + '&newthread=1'"
      >
        {{ $i18n('forum.new_thread') }}
      </a>
    </div>
    <div class="ui-widget-content ui-corner-all margin-bottom ui-padding">
      <ForumSearchField
        :group-id="groupId"
        :subforum-id="subforumId"
      />
    </div>
    <div class="head ui-widget-header ui-corner-top ui-padding">
      {{ $i18n('forum.threads') }}
    </div>
    <div class="ui-widget ui-widget-content corner-all margin-bottom ui-padding corner-bottom">
      <ul class="forum_threads linklist">
        <ThreadListEntry
          v-for="(el, index) in threads"
          :key="index"
          :thread="el"
        />

        <infinite-loading
          spinner="waveDots"
          @infinite="infiniteHandler"
        >
          <li slot="no-more" class="thread-item">
            <span v-if="!threads.length">
              {{ $i18n('forum.no_threads') }}
            </span>
          </li>
        </infinite-loading>
      </ul>
    </div>
  </div>
</template>

<script>
import ForumSearchField from './ForumSearchField'
import ThreadListEntry from './ThreadListEntry'
import InfiniteLoading from 'vue-infinite-loading'

import { listThreads } from '@/api/forum'

export default {
  components: { ForumSearchField, ThreadListEntry, InfiniteLoading },
  props: {
    groupId: { type: Number, required: true },
    subforumId: { type: Number, required: true }
  },
  data () {
    return {
      threads: [],
      offset: 0
    }
  },
  computed: {
    subforumName () {
      return this.subforumId === 1 ? 'botforum' : 'forum'
    }
  },
  methods: {
    async infiniteHandler ($state) {
      var threads = (await listThreads(this.groupId, this.subforumId, this.offset)).data
      if (threads.length) {
        this.offset += threads.length
        // sorting is awkward due to sticky threads
        // => it happens in the backend for now
        this.threads.push(...threads)
        $state.loaded()
      } else {
        $state.loaded()
        $state.complete()
      }
    }
  }
}
</script>

<style lang="scss" scoped>
</style>
