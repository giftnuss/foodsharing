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
        <p> {{ $i18n('settings.calendar.teaser') }} <a :href="$url('calendarHints')" target="_blank">{{ $url('calendarHints') }}</a></p>
        <b-button
          class="my-2"
          @click="createToken"
        >
          {{ $i18n('settings.calendar.create_token.button') }}
        </b-button>
        <b-button
          v-if="token"
          class="my-2"
          @click="removeToken"
        >
          {{ $i18n('settings.calendar.delete_token.button') }}
        </b-button>

        <ul
          v-if="token"
          class="webcal"
        >
          <li class="pb-1">
            <a :href="webcalPickups">
              {{ webcalPickups }}
            </a>
          </li>
          <li class="pb-1">
            <a :href="httpPickups">
              {{ httpPickups }}
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
import { hideLoader, pulseError, showLoader } from '@/script'
import { createApiToken, getApiToken, removeApiToken } from '@/api/calendar'
import i18n from '@/i18n'
import { BAlert, BButton } from 'bootstrap-vue'

export default {
  components: { BAlert, BButton },
  props: {
    baseUrlWebcal: { type: String, required: true },
    baseUrlHttp: { type: String, required: true },
  },
  data () {
    return {
      token: null,
    }
  },
  computed: {
    webcalPickups () {
      return this.baseUrlWebcal + this.token
    },
    httpPickups () {
      return this.baseUrlHttp + this.token
    },
  },
  async mounted () {
    showLoader()
    try {
      this.token = await getApiToken()
    } catch (e) {
      // an error means that the user has no token
      this.token = null
    }
    hideLoader()
  },
  methods: {
    async createToken () {
      let confirmed = true
      if (this.token) {
        confirmed = await this.$bvModal.msgBoxConfirm(i18n('settings.calendar.create_token.message'), {
          modalClass: 'bootstrap',
          title: i18n('settings.calendar.create_token.title'),
          cancelTitle: i18n('no'),
          okTitle: i18n('yes'),
          headerClass: 'd-flex',
          contentClass: 'pr-3 pt-3',
        })
      }
      if (confirmed) {
        showLoader()
        try {
          this.token = await createApiToken()
        } catch (e) {
          pulseError(i18n('error_unexpected'))
        }
        hideLoader()
      }
    },
    async removeToken () {
      const confirmed = await this.$bvModal.msgBoxConfirm(i18n('settings.calendar.delete_token.message'), {
        modalClass: 'bootstrap',
        title: i18n('settings.calendar.delete_token.title'),
        cancelTitle: i18n('no'),
        okTitle: i18n('yes'),
        headerClass: 'd-flex',
        contentClass: 'pr-3 pt-3',
      })
      if (confirmed) {
        showLoader()
        try {
          await removeApiToken()
          this.token = null
        } catch (e) {
          pulseError(i18n('error_unexpected'))
        }
        hideLoader()
      }
    },
  },
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
