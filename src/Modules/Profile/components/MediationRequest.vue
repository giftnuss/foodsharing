<template>
  <!-- eslint-disable vue/max-attributes-per-line -->
  <div id="mediation_request" class="popbox bootstrap m-2">
    <h3>
      {{ $i18n('profile.mediation.title', { name: foodSaverName }) }}
    </h3>

    <b-alert variant="info" show>
      <div v-if="hasLocalMediationGroup">
        {{ $i18n('profile.mediation.info', { email: mediationGroupEmail }) }}
      </div>
      <div v-if="!hasLocalMediationGroup">
        {{ $i18n('profile.mediation.no_group') }}
      </div>
    </b-alert>
  </div>
</template>

<script>
export default {
  props: {
    foodSaverName: { type: String, required: true },
    mediationGroupEmail: { type: String, required: true },
    hasLocalMediationGroup: { type: Boolean, default: false },
  },
}
</script>

<style lang="scss" scoped>
  #mediation_request {
  min-width: 50vw;
  max-width: 750px;
}
</style>
