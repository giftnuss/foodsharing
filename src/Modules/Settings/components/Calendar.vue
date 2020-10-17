<template>
  <div>
    <div class="head ui-widget-header ui-corner-top">
      {{ $i18n('settings.calendar.title') }}
    </div>
    <div class="ui-widget ui-widget-content corner-bottom margin-bottom ui-padding">
      <p>
        {{ $i18n('settings.calendar.content_1') }}
      </p>
      <p>
        {{ $i18n('settings.calendar.content_2') }}
      </p>
      <p>
        {{ $i18n('settings.calendar.content_3') }}
      </p>
      <h3>{{ $i18n('settings.calendar.link_title') }}</h3>
      <p class="webcal">
        <strong>
          <a
            :href="url + '&opts=s'"
          >
            {{ url }}&opts=s
          </a>
        </strong>
      </p>
      <p class="webcal">
        <strong>
          <a
            :href="url + '&opts=se'"
          >
            {{ url }}&opts=se {{ $i18n('settings.calendar.event_link_desc') }}
          </a>
        </strong>
      </p>
    </div>
  </div>
</template>

<script>

export default {
  props: {
    url: {
      type: String,
      default: ''
    }
  }
}
</script>

<style lang="scss" scoped>
@media (max-width: 396px) {
  .webcal {
    word-break: break-all;
  }
}
</style>
