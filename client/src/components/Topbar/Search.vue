<template>
  <div
    id="topbar-search"
    class="form-inline my-2 my-lg-0"
    style="flex-grow: 1">
    <div
      ref="inputgroup"
      class="input-group">
      <div class="input-group-prepend">
        <label
          class="input-group-text text-primary"
          for="searchfield">
          <img
            v-if="isLoading"
            src="/img/469.gif" >
          <i
            v-else
            class="fas fa-search" />
        </label>
      </div>
      <input
        id="searchfield"
        v-model="query"
        type="text"
        class="form-control text-primary"
        placeholder="Suche..."
        aria-label="Suche"
        aria-describedby="basic-addon1"
      >
    </div>
    <div
      v-if="isOpen"
      id="search-results"
      :style="resultsStyle"
      class="dropdown-menu">
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
import { user } from '@/server-data'
import clickoutMixin from '@b/mixins/clickout'
import listenOnRootMixin from '@b/mixins/listen-on-root'

export default {
  components: { SearchResults },
  mixins: [clickoutMixin, listenOnRootMixin],
  data () {
    return {
      posX: 0,
      width: 0,
      query: '',
      isOpen: false,
      isLoading: false,
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
        left: this.posX + 'px'
      }
    }
  },
  watch: {
    query (query, oldQuery) {
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
  created () {
    this.fetchIndex()
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
      let curQuery = this.query
      this.isLoading = true
      let res = await instantSearch(curQuery)
      if (curQuery !== this.query) {
        // query has changed, throw away this response
        return false
      }
      this.results = res
      this.isLoading = false
    },
    async fetchIndex () {
      this.index = await instantSearchIndex(user.token)
    },
    clickOutListener () {
      this.isOpen = false
    }
  }
}
</script>

<style lang="scss" scoped>

</style>

<style lang="scss">
#topbar-search {
    .input-group {
        margin-bottom: 0;
        width: 100% !important;
        img, i {
            height: 1em;
            width: 1em;
        }
        .input-group-text {
            background-color: white;
            border: none;
            padding: 0.1rem 0.4rem;
            font-size: .9em;
        }
        input.form-control {
            padding: 0.1rem 0.75rem;
            font-size: 1em;
            border: none;
            padding-left: 0;
            font-weight: bold;
            &:focus {
                box-shadow: none;
                border: none;
            }
        }
    }
}
#search-results {
    display: block;
    // width: 100%;
    width: 250px;
}
</style>
