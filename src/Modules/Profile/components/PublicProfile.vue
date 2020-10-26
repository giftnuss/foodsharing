<template>
  <!-- eslint-disable vue/max-attributes-per-line -->
  <div class="profile-container bootstrap">
    <b-jumbotron
      class="w-100 mx-auto my-4 position-relative"
      lead-tag="h2"
      header-level="4"
      bg-variant="light"
      border-variant="primary"
    >
      <template v-if="canPickUp" v-slot:header>
        <span class="text-secondary">
          <i class="fas fa-fw fa-check-circle" />
          {{ $i18n('profile.public.may') }}
        </span>
      </template>
      <template v-else v-slot:header>
        <span class="text-danger">
          <i class="fas fa-fw fa-times-circle" />
          {{ $i18n('profile.public.mayNot') }}
        </span>
      </template>

      <template v-slot:lead>
        {{ $i18n(canPickUp ? 'profile.public.textMay' : 'profile.public.textMayNot') }}
      </template>

      <span class="fs-name text-muted">
        {{ $i18n('profile.public.who', { name: initials, from: fromRegion }) }}
      </span>
      <span class="fs-id text-muted text-monospace bg-light">
        #{{ fsId }}
      </span>

      <hr class="my-3">

      <p>
        {{ $i18n('profile.public.cta', { name: initials }) }}
      </p>

      <b-button-group vertical size="lg">
        <b-button variant="primary" :href="$url('login') + '&ref=' + $url('urlencode', `/profile/${fsId}`)">
          <i class="fas fa-fw fa-user" />
          {{ $i18n('profile.public.login') }}
        </b-button>
        <b-button variant="secondary" :href="$url('joininfo')">
          <i class="fas fa-fw fa-rocket" />
          {{ $i18n('profile.public.join') }}
        </b-button>
      </b-button-group>
    </b-jumbotron>
  </div>
</template>

<script>
export default {
  props: {
    canPickUp: { type: Boolean, default: false },
    fromRegion: { type: String, default: '' },
    fsId: { type: Number, default: 0 },
    initials: { type: String, required: true },
  },
}
</script>

<style lang="scss" scoped>
.fs-name {
  position: absolute;
  top: 10px;
  left: 10px;
}
.fs-id {
  position: absolute;
  top: 10px;
  right: 10px;
}
</style>
