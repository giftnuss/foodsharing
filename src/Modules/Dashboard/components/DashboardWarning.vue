<template>
  <div>
    <div class="head ui-widget-header bg-danger">
      <span v-if="emailIsBouncing"> {{ $i18n('profile.warning') }} </span>
      <span v-else-if="isActivated"> {{ $i18n('dashboard.mail_activation_error_title') }} </span>
    </div>
    <div class="ui-widget-content corner-bottom margin-bottom ui-padding">
      <div
        v-if="emailIsBouncing"
      >
        {{ $i18n('profile.mailBounceWarning_1', { email: email }) }}
        <a
          :href="$url('settings')"
        >
          {{ $i18n('profile.mailBounceWarning_2') }}
        </a>
        {{ $i18n('profile.mailBounceWarning_3') }}
      </div>
      <div
        v-else-if="isActivated"
      >
        {{ $i18n('dashboard.mail_activation_error_body.part_1') }}
        <a
          :href="$url('resendActivationMail')"
        >
          {{ $i18n('dashboard.mail_activation_error_body.part_2') }}
        </a>
        {{ $i18n('dashboard.mail_activation_error_body.part_3') }}
        <a
          :href="$url('settings')"
        >
          {{ $i18n('dashboard.mail_activation_error_body.part_4') }}
        </a>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    emailIsBouncing: { type: Boolean, default: null },
    email: { type: String, default: '' },
    isActivated: { type: Boolean, default: null },
  },
}
</script>

<style scoped>

</style>
