<template>
  <div class="banana-list w-100">
    <BananaListEntry
      v-for="b in bananas"
      :key="b.id"
      :author-id="b.id"
      :author-name="b.name"
      :avatar="b.photo"
      :created-at="b.createdAt"
      :text="b.msg"
    />
  </div>
</template>

<script>
import BananaListEntry from './BananaListEntry'

export default {
  components: { BananaListEntry },
  props: {
    bananas: { type: Array, default: () => { return [] } },
  },
}
</script>

<style lang="scss" scoped>
</style>
