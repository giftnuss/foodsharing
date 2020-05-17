<template>
  <a
    :href="linkUrl"
    :aria-label="$i18n('home.title')"
    class="navbar-brand brand"
  >
    <span class="logo-text">
      food<span class="green">shar<span class="apple">i</span>ng</span>
    </span>
    <span class="d-md-none">&nbsp;&nbsp;&nbsp;&nbsp;</span>
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
.brand {
    font-family: 'Alfa Slab One',serif;
    color: #ffffff;
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
    @media (max-width: 768px)  {
      // background-image: url('/img/fsgabelgwrgbklein.png');
      background-image: url('/img/fsgabelgwrgbklein.png');
      background-size: contain;
      background-repeat: no-repeat;
      .logo-text {
        display:none;
      }
    }
}
</style>
