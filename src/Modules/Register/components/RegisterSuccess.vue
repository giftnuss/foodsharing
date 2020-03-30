<template>
  <div class="my-1">
    <div class="col-sm-auto">
      <div class="msg-inside info">
        <i class="fas fa-info-circle" /> {{ $i18n('register.join_success_message') }}
      </div>
      <b-button
        class="btn btn-secondary ml-3 mt-3"
        @click="$emit('loadLogin')"
      >
        {{ $i18n('login.login_button_label') }}
      </b-button>
    </div>
  </div>
</template>
