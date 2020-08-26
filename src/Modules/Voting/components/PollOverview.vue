<template>
  <div class="head ui-widget-header ui-corner-top">
    {{ poll.name }}
  </div>
  <div class="ui-widget ui-widget-content corner-bottom margin-bottom ui-padding">
    {{ poll.description }}
  </div>
</template>

<script>

export default {
  props: {
    poll: {
      type: Object,
      required: true
    }
  }
}
</script>

<style scoped lang="scss">
</style>
