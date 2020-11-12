<template>
  <a
    :href="linkUrl"
    :aria-label="$i18n('home.title')"
    class="navbar-brand brand"
  >
    <span class="logo-text d-none d-lg-inline-block">
      food<span class="green">shar<span class="apple">i</span>ng</span>
    </span>
    <span class="logo-text d-lg-none">
      f<span class="green">s</span>
    </span>
  </a>
</template>
<script>
export default {
  props: {
    linkUrl: {
      type: String,
      default: '',
    },
  },
}
</script>
<style lang="scss" scoped>
.brand {
    font-family: 'Alfa Slab One',serif;
    color: var(--primary);
    margin-right: 0;
    margin-left: 5px;
    font-size: 1.1rem;
    span.green {
        color: #64ae25;
        position: relative;
    }
    span.logo-text:hover .apple::before {
      content: '♥';
      color: red;
      position: absolute;
      font-size: 0.5em;
    }
}
</style>
