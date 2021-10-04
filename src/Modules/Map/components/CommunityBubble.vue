<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <h3>{{ name }}</h3>
      <Markdown :source="desc" />
    </div>
  </div>
</template>

<script>
import Markdown from '@/components/Markdown/Markdown'

export default {
  components: { Markdown },
  props: {
    regionId: { type: Number, required: true },
    name: { type: String, default: '' },
    desc: { type: String, default: '' },
  },
}
</script>
