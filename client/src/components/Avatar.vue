

<template>
  <span
    :style="`background-image:url(${url});`"
    :class="`avatar size-${size} sleepmode-${sleepStatus}`"
  >
    &nbsp;
  </span>
</template>

<script>
export default {
  props: {
    url: {},
    size: {},
    sleepStatus: {}
  }
}
</script>
