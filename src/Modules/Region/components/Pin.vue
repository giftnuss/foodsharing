<template>
  <div class="bootstrap">
    <div class="card rounded">
      <div class="card-header text-white bg-primary">
        <h4 class="m-1 text-truncate">
          {{ $i18n('regionPin.header_page', { bezirk: regionName }) }}
        </h4>
      </div>
      <div class="rounded pa-3">
        <b-form-input
          id="inlat"
        >
          {{ $i18n('regionPin.lat') }}
        </b-form-input>
        <b-form-input
          id="inlon"
          :state="lon ? false : null"
        >
          {{ $i18n('regionPin.lon') }}
        </b-form-input>
        <b-form-textarea
          id="tadesc"
          v-model="text"
          placeholder="Hier eine Beschreibung des Ortsgruppe angeben. Diese wird auf der Karte angezeigt..."
          rows="6"
          max-rows="9"
        />

        <b-button
          class="text-right mt-2"
          variant="secondary"
          size="sm"
          @click="trySendOptions"
        >
          {{ $i18n('regionPin.save') }}
        </b-button>
      </div>
    </div>
  </div>
</template>
<script>
import { BFormInput, BButton, BFormTextarea } from 'bootstrap-vue'
import { setRegionPin } from '@/api/regions'
import { hideLoader, pulseError, pulseInfo, showLoader } from '@/script'
import i18n from '@/i18n'

export default {
  components: { BFormInput, BButton, BFormTextarea },
  props: {
    lat: {
      type: String,
      default: '',
    },
    lon: {
      type: String,
      default: '',
    },
    desc: {
      type: String,
      default: '',
    },
    regionId: { type: Number, required: true },
    regionName: {
      type: String,
      default: '',
    },
  },
  data () {
    return {
      inlat: this.lat,
      inlon: this.lon,
      tadesc: this.desc,
    }
  },
  methods: {
    async trySendOptions () {
      showLoader()
      try {
        await setRegionPin(this.regionId, this.lat, this.lon, this.desc)
        pulseInfo(i18n('regionOptions.success'))
      } catch (err) {
        console.error(err)
        pulseError(i18n('error_unexpected'))
      }
      hideLoader()
    },
  },
}
</script>
