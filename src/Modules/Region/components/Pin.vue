<template>
  <div class="bootstrap">
    <div class="card rounded">
      <div class="card-header text-white bg-primary">
        <h4 class="m-1 text-truncate">
          {{ $i18n('regionPin.header_page', { bezirk: regionName }) }}
        </h4>
      </div>
      <b-form
        :class="{disabledLoading: isLoading,'card-body': true}"
      >
        <div class="rounded pa-3">
          <b-form-group
            id="input-group-lat"
            :label="$i18n('regionPin.lat')"
            label-for="input_lat"
          >
            <b-form-input
              id="input_lat"
              v-model="inlat"
              required
            />
          </b-form-group>

          <b-form-group
            id="input-group-lon"
            :label="$i18n('regionPin.lon')"
            label-for="input_lon"
          >
            <b-form-input
              id="input_lon"
              v-model="inlon"
            />
          </b-form-group>

          <b-form-group
            id="input-group-lon"
            :label="$i18n('regionPin.desc')"
            label-for="text_description"
          >
            <div
              class="mb-2 ml-2"
              v-html="$i18n('forum.markdown_description')"
            />
            <b-form-textarea
              id="text_description"
              v-model="tadesc"
              :placeholder="$i18n('regionPin.text_desc')"
              rows="12"
            />
          </b-form-group>

          <b-button
            class="text-right mt-2"
            @click="trySendPin"
          >
            {{ $i18n('regionPin.save') }}
          </b-button>
        </div>
      </b-form>
    </div>
  </div>
</template>
<script>
import { BFormInput, BButton, BFormTextarea } from 'bootstrap-vue'
import { setRegionPin } from '@/api/regions'
import { pulseError, pulseInfo } from '@/script'
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
      isLoading: false,
      inlat: this.lat,
      inlon: this.lon,
      tadesc: this.desc,
    }
  },
  methods: {
    async trySendPin () {
      this.isLoading = true
      try {
        await setRegionPin(this.regionId, this.inlat, this.inlon, this.tadesc)
        pulseInfo(i18n('regionPin.success'))
      } catch (err) {
        console.error(err)
        pulseError(i18n('error_unexpected'))
      }
      this.isLoading = false
    },
  },
}
</script>
