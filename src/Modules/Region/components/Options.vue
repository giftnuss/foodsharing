<template>
  <div class="bootstrap card">
    <div class="card-header text-white bg-primary">
      <div class="row m-1">
        <h4 class="text-truncate">
          {{ $i18n('regionOptions.header_page', {bezirk: regionName}) }}
        </h4>
      </div>
    </div>
    <div class="card m-2">
      <div>
        <b-form-checkbox
          id="enableReportButton"
          v-model="isReportButtonEnabled"
        >
          Meldungsknopf auf den Profilen der Stammfoodsaver des Bezirkes aktivieren.
        </b-form-checkbox>
        <b-form-checkbox
          id="enableMediationButton"
          v-model="isMediationButtonEnabled"
        >
          Mediationsknopf auf den Profilen der Stammfoodsaver des Bezirkes aktivieren.
        </b-form-checkbox>
        <b-button
          class="text-right"
          variant="secondary"
          size="sm"
          @click="trySendOptions"
        >
          {{ $i18n('regionOptions.save') }}
        </b-button>
      </div>
    </div>
  </div>
</template>
<script>
import { setRegionOptions } from '@/api/regions'
import { pulseError, pulseInfo } from '@/script'
import i18n from '@/i18n'

export default {
  props: {
    isReportButtonEnabled: { type: Boolean, default: false },
    isMediationButtonEnabled: { type: Boolean, default: false },
    regionId: { type: Number, required: true },
    regionName: {
      type: String,
      default: '',
    },
  },
  methods: {
    async trySendOptions () {
      try {
        await setRegionOptions(this.regionId, this.isReportButtonEnabled, this.isMediationButtonEnabled)
        pulseInfo(i18n('regionOptions.sent'))
      } catch (err) {
        console.error(err)
        pulseError(i18n('error_unexpected'))
      }
    },
  },
}
</script>
