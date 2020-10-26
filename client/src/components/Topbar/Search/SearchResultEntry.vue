<template>
  <a
    :href="href"
    class="dropdown-item text-truncate search-result"
  >
    <div
      v-if="image"
      :style="{backgroundImage: `url('${image}')`}"
      class="bg-primary image"
    />
    {{ title }}
    <small
      v-if="teaser"
      class="text-truncate"
    >
      <i
        v-if="teaserIcon"
        :class="`fa-fw ${teaserIcon}`"
      />
      {{ teaser }}
    </small>
  </a>
</template>
<script>
export default {
  props: {
    href: {
      type: String,
      default: null,
    },
    title: {
      type: String,
      default: '',
    },
    teaser: {
      type: String,
      default: '',
    },
    teaserIcon: {
      type: String,
      default: '',
    },
    image: {
      type: String,
      default: '',
    },
  },
}
</script>

<style lang="scss" scoped>
.search-result small {
    display: block;
    margin-top: -0.1em;
}
.image {
    float: left;
    height: 2em;
    width: 2em;
    background-size: cover;
    margin-right: 0.4em;
    margin-top: 0.2em;
}
</style>
