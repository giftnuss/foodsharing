<template>
  <div>
    <div class="head ui-widget-header ui-corner-top">
      {{ $i18n('settings.calendar.title') }}
    </div>
    <div class="ui-widget ui-widget-content corner-bottom margin-bottom ui-padding">
      <p>
        {{ $i18n('settings.calendar.content') }}
      </p>
      <h3>{{ $i18n('settings.calendar.link_title') }}</h3>
      <p class="webcal">
        <strong>
          <a
            :href="webcalPickups"
          >
            {{ webcalPickups }}
          </a>
        </strong>
      </p>
      <p class="webcal">
        <strong>
          <a
            :href="webcalPickupsAndEvents"
          >
            {{ webcalPickupsAndEvents }} {{ $i18n('settings.calendar.event_link_desc') }}
          </a>
        </strong>
      </p>
    </div>
  </div>
</template>

<script>

export default {
  props: {
    url: { type: String, default: '' }
  },
  computed: {
    webcalPickups () {
      return this.url + '&opts=s'
    },
    webcalPickupsAndEvents () {
      return this.url + '&opts=se'
    }
  }
}
</script>

<style lang="scss" scoped>
@media (max-width: 396px) {
  .webcal {
    word-break: break-all;
    overflow-wrap: break-word;
  }
}
</style>
