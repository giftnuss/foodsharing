
<template>
  <div
    :style="style"
    :class="{avatarDiv: true, sleeping: sleepStatus, sleeping35: size === 35, sleeping50: size === 50, sleeping130: size === 130}"
  />
</template>

<script>

export default {
  props: {
    url: {
      type: String,
      default: null
    },
    size: {
      type: Number,
      default: 35
    },
    sleepStatus: {
      type: Number,
      default: 0
    }
  },
  computed: {
    avatarUrl () {
      let prefix = ''
      switch (this.size) {
        case 35:
          prefix = 'mini_q_'
          break
        case 50:
          prefix = '50_q_'
          break
        case 130:
          prefix = '130_q_'
          break
      }
      if (this.url) {
        return '/images/' + prefix + this.url
      } else {
        return '/img/' + prefix + 'avatar.png'
      }
    },
    style () {
      return {
        width: `${this.size}px`,
        height: `${this.size}px`,
        backgroundImage: `url(${this.avatarUrl})`
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
  height: 100%;
  width: 100%;
  background-repeat: no-repeat;
  background-size: cover;
  position: absolute;
  top: 0;
  left: 10%;
}
.sleeping35::after {
  background-image: url('/img/sleep35x35.png');
}
.sleeping50::after {
  background-image: url('/img/sleep50x50.png');
}
.sleeping130::after {
  background-image: url('/img/sleep130x130.png');
}
</style>
