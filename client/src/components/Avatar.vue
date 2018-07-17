

<template>
  <div
    :style="style"
    :class="{avatarDiv: true, sleeping: sleepStatus}"
   />
</template>

<script>
export default {
  props: {
    url: {},
    size: {},
    sleepStatus: {}
  },
  computed: {
    style() {
      return {
        width: this.size+'px',
        height: this.size+'px',
        backgroundImage: `url(${this.url && false ? this.url : '/img/130_q_avatar.png'})`
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
