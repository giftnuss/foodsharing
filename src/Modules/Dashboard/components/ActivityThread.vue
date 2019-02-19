<template>
  <div class="">
    <ul class="linklist">
      <ActivityPost v-for="el in updates" :key="el.time" :type="el.type" :data="el.data"/>
    </ul>
  </div>
</template>

<script>
import { getUpdates } from "@/api/dashboard";
import ActivityPost from './ActivityPost'

export default {
  components: { ActivityPost },
  props: {},
  data() {
    return {
      updates: []
    };
  },
  async created() {
    this.updates = await getUpdates(0);
  }
};
</script>

<style lang="scss" scoped>
</style>
