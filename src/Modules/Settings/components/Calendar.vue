<!-- eslint-disable vue/max-attributes-per-line -->
<template>
  <div class="settings-calendar">
    <div class="head ui-widget-header">
      {{ $i18n('settings.calendar.title') }}
    </div>

    <div class="ui-widget-content corner-bottom margin-bottom ui-padding">
      <h3 class="heading">
        <i class="fas fa-fw fa-lightbulb" />
        {{ $i18n('settings.calendar.link_title') }}
      </h3>
      <div class="bootstrap">
        <p> {{ $i18n('settings.calendar.teaser') }} </p>
        <ul class="webcal">
          <li class="pb-1">
            <a :href="webcalPickups">
              {{ webcalPickups }}
            </a>
          </li>
          <li class="pb-1">
            <a :href="webcalPickupsAndEvents">
              {{ webcalPickupsAndEvents }} {{ $i18n('settings.calendar.event_link_desc') }}
            </a>
          </li>
        </ul>

        <b-alert variant="warning" show>
          {{ $i18n('settings.calendar.token-warning') }}
        </b-alert>

        <b-alert variant="secondary" show>
          {{ $i18n('settings.calendar.sync') }}
        </b-alert>
      </div>
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
.heading {
  padding: 0.2rem 0.1rem;
  font-family: 'Alfa Slab One', serif;
  font-weight: normal;
  font-size: 1rem;
  color: var(--fs-brown);
}

.webcal {
  font-weight: bolder;

  // --breakpoint-xs
  @media (max-width: 576px) {
    word-break: break-all;
    overflow-wrap: break-word;
  }

  li a {
    color: var(--secondary);
  }
}
</style>
