<template>
  <b-nav-item :href="url">
    <i :class="`fas ${icon}`" />
    {{ title }}
  </b-nav-item>
</template>

<script>
export default {
  props: {
    url: { type: String, default: undefined },
    icon: { type: String, default: undefined },
    title: { type: String, default: undefined }
  }
}
</script>
