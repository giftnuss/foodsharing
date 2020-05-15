<template>
  <div
    id="topbar-search"
    class="form my-2 my-lg-0 flex-grow-1"
  >
    <div
      ref="inputgroup"
      class="input-group input-group-sm"
    >
      <div class="input-group-prepend">
        <label
          id="searchfield-label"
          :aria-label="$i18n('search.title')"
          class="input-group-text text-primary"
          for="searchfield"
        >
          <img
            v-if="isLoading"
            src="/img/469.gif"
          >
          <i
            v-else
            class="fas fa-search"
          />
        </label>
      </div>
      <input
        id="searchfield"
        v-model="query"
        :placeholder="$i18n('search.placeholder')"
        type="text"
        class="form-control text-primary"
        aria-labelledby="searchfield-label"
        aria-placeholder=""
      >
    </div>
    <div
      v-if="isOpen"
      id="search-results"
      :style="resultsStyle"
      class="dropdown-menu"
    >
      <search-results
        :users="results.users || []"
        :regions="results.regions || []"
        :stores="results.stores || []"
        :my-groups="index.myGroups"
        :my-regions="index.myRegions"
        :my-stores="index.myStores"
        :my-buddies="index.myBuddies"
        :query="query"
        :is-loading="isLoading"
      />
    </div>
  </div>
</template>

<script>
import SearchResults from './SearchResults'
import { instantSearch, instantSearchIndex } from '@/api/search'
import clickOutMixin from 'bootstrap-vue/esm/mixins/click-out'
import listenOnRootMixin from 'bootstrap-vue/esm/mixins/listen-on-root'

export default {
  components: { SearchResults },
  mixins: [clickOutMixin, listenOnRootMixin],
  data () {
    return {
      posX: 0,
      width: 0,
      query: '',
      isOpen: false,
      isLoading: false,
      isIndexLoaded: false,
      results: {
        stores: [],
        users: [],
        regions: []
      },
      index: {
        myStores: [],
        myGroups: [],
        myRegions: [],
        myBuddies: []
      }
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
    query (query, oldQuery) {
      if (!this.isIndexLoaded) {
        this.fetchIndex()
      }
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
      this.posX = this.$refs.inputgroup.getBoundingClientRect().left
      this.width = this.$refs.inputgroup.getBoundingClientRect().width
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
      const res = await instantSearch(curQuery)
      if (curQuery !== this.query) {
        // query has changed, throw away this response
        return false
      }
      this.results = res
      this.isLoading = false
    },
    async fetchIndex () {
      this.isIndexLoaded = true
      this.index = await instantSearchIndex()
    },
    clickOutListener () {
      this.isOpen = false
    }
  }
}
</script>

<style lang="scss" scoped>
  #search-results {
    display: block;
    width: 250px;
  }
</style>
