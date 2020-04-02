<template>
  <div>
    <ul class="linklist">
      <ActivityPost
        v-for="(el, index) in hideUnwanted(updates)"
        :key="index"
        :type="el.type"
        :data="el.data"
      />
      <infinite-loading
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
            {{ $i18n('dashboard.no_more_updates') }}
          </span>
        </li>
      </infinite-loading>
    </ul>
  </div>
</template>

<script>
import { getUpdates } from '@/api/dashboard'
import ActivityPost from './ActivityPost'
import InfiniteLoading from 'vue-infinite-loading'

/* TODOs
- make cog work again
*/

export default {
  components: { ActivityPost, InfiniteLoading },
  props: {
    displayedTypes: {
      type: Array,
      default: () => { return ['store', 'forum', 'mailbox', 'foodsharepoint', 'friendWall', 'foodbasket'] }
    }
  },
  data () {
    return {
      updates: [],
      page: 0
    }
  },
  methods: {
    hideUnwanted (updates) {
      return updates.filter(a => this.displayedTypes.indexOf(a.type) !== -1)
    },
    delay (howLong) {
      return new Promise(resolve => setTimeout(resolve, howLong))
    },
    async infiniteHandler ($state) {
      var res = await getUpdates(this.page)
      var filtered = this.hideUnwanted(res)
      if (filtered.length) {
        this.page += 1
        res.sort((a, b) => {
          return (b.time_ts || b.data.time_ts) - (a.time_ts || a.data.time_ts)
        })
        this.updates.push(...res)
        await this.delay(1000)
        $state.loaded()
      } else if (res.length) {
        // There are more results, but the next page would be
        // empty with the current filter settings... ??? WHAT TO DO ???
        // ugly solution right now: wait for a longer duration, then retry
        await this.delay(60 * 1000) // 1 min
        $state.loaded()
      } else {
        $state.loaded()
        $state.complete()
      }
    },
    async reloadData () {
      this.page = 0
      this.updates = await getUpdates(this.page)
      this.updates.sort((a, b) => {
        return (b.time_ts || b.data.time_ts) - (a.time_ts || a.data.time_ts)
      })
    }
  }
}
</script>

<style lang="scss" scoped>
</style>
