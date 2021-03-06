<template>
  <div>
    <ul class="linklist">
      <ActivityPost
        v-for="(post, index) in hideUnwanted(updates)"
        :key="index"
        v-bind="post"
      />
      <infinite-loading
        ref="infiniteLoading"
        spinner="waveDots"
        @infinite="infiniteHandler"
      >
        <li
          slot="no-results"
          class="activity-item"
        >
          <span>
            {{ $i18n('dashboard.no_updates') }}
          </span>
        </li>
        <li
          slot="no-more"
          class="activity-item"
        >
          <span>
            {{ $i18n('dashboard.no_more_updates_' + currentFilter) }}
          </span>
        </li>
      </infinite-loading>
    </ul>
  </div>
</template>

<script>
import { getUpdates } from '@/api/dashboard'
import ActivityPost from './ActivityPost'
import { allFilterTypes } from './ActivityFilter'
import InfiniteLoading from 'vue-infinite-loading'
import { parseISO, compareDesc } from 'date-fns'

export default {
  components: { ActivityPost, InfiniteLoading },
  props: {
    displayedTypes: {
      type: Array,
      default: () => { return allFilterTypes },
    },
  },
  data () {
    return {
      updates: [],
      page: 0,
    }
  },
  computed: {
    currentFilter () {
      if (this.displayedTypes.length === 1) {
        return this.displayedTypes[0]
      } else {
        // this assumes that no other filter enables more than one type!
        return 'all'
      }
    },
  },
  methods: {
    resetInfinity () {
      // from https://github.com/PeachScript/vue-infinite-loading/issues/123#issuecomment-357129636
      // this causes the loader to start looking for data again, when in completed state
      this.$refs.infiniteLoading.stateChanger.reset()
    },
    hideUnwanted (updates) {
      return updates.filter(a => this.displayedTypes.indexOf(a.type) !== -1)
    },
    async infiniteHandler ($state) {
      const updates = await getUpdates(this.page)
      updates.forEach(function (u, index, array) {
        array[index].time = parseISO(array[index].time)
      })
      const filtered = this.hideUnwanted(updates)
      if (filtered.length) {
        this.page += 1
        updates.sort((a, b) => {
          return compareDesc(a.time, b.time)
        })
        this.updates.push(...updates)
        $state.loaded()
      } else {
        $state.loaded()
        $state.complete()
      }
    },
    async reloadData () {
      this.resetInfinity()
      this.page = 0
      this.updates = []
    },
  },
}
</script>

<style lang="scss" scoped>
/deep/ ul.linklist li {
  margin-bottom: 0.5rem;
  background-color: var(--white);
  padding: 0.5rem;
  border-radius: 6px;
}
</style>
