<template>
  <div class="bootstrap">
    <div class="card rounded">
      <div class="card-header text-white bg-primary">
        <h4 class="m-1 text-truncate">
          {{ $i18n('regionOptions.header_page', { bezirk: regionName }) }}
        </h4>
      </div>
      <div class="rounded p-3">
        <b-form-checkbox
          id="enableReportButton"
          v-model="isReportButtonEnabled"
        >
          {{ $i18n('regionOptions.enableReportButton') }}
        </b-form-checkbox>
        <b-form-checkbox
          id="enableMediationButton"
          v-model="isMediationButtonEnabled"
          class="mt-1"
        >
          {{ $i18n('regionOptions.enableMediationButton') }}
        </b-form-checkbox>
        <b-button
          class="text-right mt-2"
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
import { hideLoader, pulseError, pulseInfo, showLoader } from '@/script'
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
      showLoader()
      try {
        await setRegionOptions(this.regionId, this.isReportButtonEnabled, this.isMediationButtonEnabled)
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
