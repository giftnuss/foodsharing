<template>
  <div
    class="avatar"
    :class="[{'sleeping': sleepStatus}, `sleep${size}`]"
    :style="wrapperStyle"
  >
    <img
      :src="avatarUrl"
      :class="imgClass"
      style="height: 100%"
      :style="imgStyle"
    >
  </div>
</template>

<script>

export default {
  props: {
    url: {
      type: String,
      default: null,
    },
    size: {
      type: Number,
      default: 35,
    },
    sleepStatus: {
      type: Number,
      default: 0,
    },
    imgClass: {
      type: String,
      default: '',
    },
    rounded: {
      type: Boolean,
      default: true,
    },
    autoScale: {
      type: Boolean,
      default: true,
    },
  },
  computed: {
    avatarUrl () {
      const prefix = {
        35: 'mini_q_',
        50: '50_q_',
        130: '130_q_',
      }[this.size] || ''
      if (this.url) {
        return '/images/' + prefix + this.url
      } else {
        return '/img/' + prefix + 'avatar.png'
      }
    },
    wrapperStyle () {
      const styles = {}
      if (this.autoScale) {
        styles.height = '100%'
        styles.width = 'auto'
      }
      return styles
    },
    imgStyle () {
      const styles = {}
      if (this.rounded) {
        styles['border-radius'] = '5px'
      }
      return styles
    },
  },
}
</script>

<style lang="scss" scoped>
.avatar {
  position: relative;
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
  top: -10%;
  left: -10%;
}
.sleep35::after {
  background-image: url('/img/sleep35x35.png');
}
.sleep50::after {
  background-image: url('/img/sleep50x50.png');
}
.sleep130::after {
  background-image: url('/img/sleep130x130.png');
}
</style>
