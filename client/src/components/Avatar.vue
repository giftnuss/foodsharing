
<template>
  <div
    :style="style"
    :class="{avatarDiv: true, sleeping: sleepStatus}"
  />
</template>

<script>
import { AVATAR_DEFAULT } from '@/consts'
import { img } from '@/script'

export default {
  props: {
    url: {
      type: String,
      default: null
    },
    size: {
      type: Number,
      default: 130
    },
    sleepStatus: {
      type: Number,
      default: 0
    }
  },
  computed: {
    style () {
      return {
        width: `${this.size}px`,
        height: `${this.size}px`,
        backgroundImage: `url(${this.url ? img(this.url, '130') : AVATAR_DEFAULT})`
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.avatarDiv {
  position: relative;
  // margin: auto;
  display: inline-block;
  background-size: cover;
}
.sleeping::after {
  content: '';
  display: block;
  height: 50%;
  width: 50%;
  background-image: url('/img/sleep130x130.png');
  background-repeat: no-repeat;
  background-size: cover;
  position: absolute;
  top: 0;
  left: 10%;
}
</style>
