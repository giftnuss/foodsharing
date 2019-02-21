<template>
  <div class>
    <ul class="linklist">
      <ActivityPost v-for="(el, index) in updates" :key="index" :type="el.type" :data="el.data"/>
      <infinite-loading spinner="waveDots" @infinite="infiniteHandler"></infinite-loading>
    </ul>
  </div>
</template>

<script>
import { getUpdates } from "@/api/dashboard";
import ActivityPost from "./ActivityPost";
import InfiniteLoading from "vue-infinite-loading";

/* TODOs
- sort updates by time
- make cog work again
*/

export default {
  components: { ActivityPost, InfiniteLoading },
  props: {},
  data() {
    return {
      updates: [],
      page: 0
    };
  },
  async created() {
    this.updates = await getUpdates(0);
    this.updates.sort((a, b) => {
      return b.data.time_ts - a.data.time_ts;
    });
    // activity.initOption(this.updates)
  },
  methods: {
    async infiniteHandler($state) {
      var res = await getUpdates(0)
      if (res.length) {
        this.page += 1
        res.sort((a, b) => {
          return b.data.time_ts - a.data.time_ts;
        });
        this.updates.push(...res)
        $state.loaded()
      } else {
        $state.complete()
      }
    }
  }
};
</script>

<style lang="scss" scoped>
</style>
