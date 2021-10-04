<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <h3>{{ name }}</h3>
      {{ desc }}
    </div>
  </div>
</template>

<script>
export default {
  props: {
    regionId: { type: Number, required: true },
    name: { type: String, default: '' },
    desc: { type: String, default: '' },
  },
}
</script>
