<template>
  <div
    id="forum-search"
    class="form m-2"
  >
    <div
      ref="foruminputgroup"
      class="input-group input-group-sm"
    >
      <span class="input-group-prepend">
        <label
          id="forum-searchfield-label"
          :aria-label="$i18n('search.title')"
          class="input-group-text text-primary"
          for="forum-searchfield"
        >
          <img
            v-if="isLoading"
            src="/img/469.gif"
            alt="loading"
          >
          <i
            v-else
            class="fas fa-search"
          />
        </label>
        <input
          id="forum-searchfield"
          v-model="query"
          :placeholder="$i18n('search.placeholder')"
          type="text"
          class="form-control text-primary w-50"
          aria-labelledby="forum-searchfield-label"
          aria-placeholder=""
        >
      </span>
    </div>
    <div
      v-if="isOpen"
      id="forum-search-results"
      :style="resultsStyle"
      class="dropdown-menu"
    >
      <forum-search-results
        :themes="themes || []"
        :group-id="groupId"
        :subforum-id="subforumId"
        :query="query"
        :is-loading="isLoading"
      />
    </div>
  </div>
</template>

<script>
import ForumSearchResults from './ForumSearchResults'
import { searchForum } from '@/api/search'
import clickOutMixin from 'bootstrap-vue/esm/mixins/click-out'
import listenOnRootMixin from 'bootstrap-vue/esm/mixins/listen-on-root'

export default {
  components: { ForumSearchResults },
  mixins: [clickOutMixin, listenOnRootMixin],
  props: {
    groupId: {
      type: Number,
      default: -1
    },
    subforumId: {
      type: Number,
      required: true
    }
  },
  data () {
    return {
      posX: 0,
      width: 0,
      query: '',
      isOpen: false,
      isLoading: false,
      themes: []
    }
  },
  computed: {
    resultsStyle () {
      return {
        left: `${this.posX}px`
      }
    }
  },
  watch: {
    query (query) {
      if (query.trim().length > 2) {
        this.open()
        this.delayedFetch()
      } else if (query.trim().length) {
        clearTimeout(this.timeout)
        this.open()
        this.isLoading = false
      } else {
        clearTimeout(this.timeout)
        this.close()
        this.isLoading = false
      }
    }
  },
  mounted () {
    // close the result box if another dropdown menu gets opened
    this.listenOnRoot('bv::dropdown::shown', this.close)
  },
  methods: {
    open () {
      this.posX = this.$refs.foruminputgroup.getBoundingClientRect().left
      this.width = this.$refs.foruminputgroup.getBoundingClientRect().width
      this.isOpen = true
    },
    delayedFetch () {
      if (this.timeout) {
        clearTimeout(this.timeout)
        this.timer = null
      }
      this.timeout = setTimeout(() => {
        this.fetch()
      }, 200)
    },
    close () {
      this.isOpen = false
    },
    async fetch () {
      const curQuery = this.query
      this.isLoading = true
      const res = await searchForum(this.groupId, this.subforumId, curQuery)
      if (curQuery !== this.query) {
        // query has changed, throw away this response
        return false
      }
      this.themes = res
      this.isLoading = false
    },
    clickOutListener () {
      this.isOpen = false
    }
  }
}
</script>

<style lang="scss" scoped>
  #forum-search-results {
    display: block;
    width: 250px;
  }
</style>
