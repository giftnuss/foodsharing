<template>
  <div>
    <div class="head ui-widget-header bg-danger">
      <span v-if="emailIsBouncing"> {{ $i18n('profile.warning') }} </span>
      <span v-else-if="isNotActivated"> {{ $i18n('dashboard.mail_activation_error_title') }} </span>
    </div>
    <div class="ui-widget-content corner-bottom margin-bottom ui-padding">
      <div
        v-if="emailIsBouncing"
      >
        {{ $i18n('profile.mail_bounce.warning_1', { email: email }) }}
        <a
          :href="$url('settings')"
        >
          {{ $i18n('profile.mail_bounce.warning_2') }}
        </a>
        {{ $i18n('profile.mail_bounce.warning_3') }}
        <a href="https://foodsharing.freshdesk.com/support/solutions/articles/77000299947-e-mail-sperre-im-profil"> {{ $i18n('profile.mail_bounce.warning_4') }}</a>
      </div>
      <div
        v-else-if="isNotActivated"
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
    emailIsBouncing: { type: Boolean, required: true },
    email: { type: String, default: '' },
    isNotActivated: { type: Boolean, required: true },
  },
}
</script>

<style scoped>

</style>
