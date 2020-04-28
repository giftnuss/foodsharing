<template>
  <a
    :href="linkUrl"
    :aria-label="$i18n('home.title')"
    class="navbar-brand mr-2"
  >
    food<span>shar<span>i</span>ng</span>
  </a>
</template>
<script>
export default {
  props: {
    linkUrl: {
      type: String,
      default: ''
    }
  }
}
</script>
<style lang="scss" scoped>
.navbar-brand {
    font-family: 'Alfa Slab One',serif;
    color: #ffffff;
    margin-right: 0;
    font-size: 1.1rem;
    span {
        color: #64ae25;
    }
    span {
        position: relative;
        &:hover {
            span::before {
                content: '♥';
                color: red;
                position: absolute;
                font-size: 0.5em;
                margin-top: -0.04em;
                margin-left: -0.085em;
            }
        }
    }
    @media (max-width: 680px) {
        font-size: 0.9rem;
        &.small {
            font-size: 0.4rem;
        }
    }
}
</style>
