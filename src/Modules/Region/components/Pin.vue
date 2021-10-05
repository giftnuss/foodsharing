<template>
  <div class="bootstrap">
    <div class="card rounded">
      <div class="card-header text-white bg-primary">
        <h4 class="m-1 text-truncate">
          {{ $i18n('regionPin.header_page', { bezirk: regionName }) }}
        </h4>
      </div>
      <div class="rounded pa-3">
        <b-form-group
          id="input-group-lat"
          label="Längengrad"
          label-for="input_lat"
        >
          <b-form-input
            id="input_lat"
            v-model="inlat"
            required
          />
        </b-form-group>

        <b-form-input
          id="input_lon"
          v-model="inlon"
        />
        {{ $i18n('regionPin.desc') }}
        <b-form-textarea
          id="text_description"
          v-model="tadesc"
          placeholder="Hier eine Beschreibung des Ortsgruppe angeben. Diese wird auf der Karte angezeigt..."
          rows="6"
          max-rows="9"
        />

        <b-button
          class="text-right mt-2"
          variant="secondary"
          size="sm"
          @click="trySendPin"
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
    async trySendPin () {
      showLoader()
      try {
        await setRegionPin(this.regionId, this.inlat, this.inlon, this.tadesc)
        pulseInfo(i18n('regionPin.success'))
      } catch (err) {
        console.error(err)
        pulseError(i18n('error_unexpected'))
      }
      hideLoader()
    },
  },
}
</script>
