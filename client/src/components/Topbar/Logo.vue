<template>
  <a
    :href="$url('home')"
    :aria-label="$i18n('home.title')"
    class="navbar-brand mr-2"
  >
    food<span>shar<span>i</span>ng</span>
  </a>
</template>
<script>
export default {
  props: { small: { type: Boolean } }
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
    span span {
        position: relative;
        &:hover::before {
            content: '♥';
            color: red;
            position: absolute;
            font-size: 0.5em;
            margin-top: -0.04em;
            margin-left: -0.085em;
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
