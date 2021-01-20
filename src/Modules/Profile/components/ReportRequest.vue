<template>
  <!-- eslint-disable vue/max-attributes-per-line -->
  <div id="report_request" class="popbox bootstrap m-2">
    <h3>
      {{ $i18n('profile.report.title', { name: foodSaverName }) }}
    </h3>
    <b-form-textarea
      v-model="reportText"
      class="mb-2"
      max-rows="8"
      size="sm"
    />
    <b-button
      class="text-right"
      variant="secondary"
      size="sm"
      @click="trySendReport"
    >
      {{ $i18n('profile.report.send') }}
    </b-button>
  </div>
</template>

<script>
import { addReport } from '@/api/report'
import { pulseError } from '@/script'
import i18n from '@/i18n'

export default {
  props: {
    foodSaverName: { type: String, required: true },
    reportedId: { type: Number, required: true },
    reporterId: { type: Number, required: true },
  },
  data () {
    return {
      reportText: '',
    }
  },

  methods: {
    async trySendReport () {
      try {
        await addReport(this.reportedId, this.reporterId, 0, 'some reason text', this.reportText.trim())
      } catch (err) {
        console.error(err)
        pulseError(i18n('error_unexpected'))
      }
    },
  },
}
</script>

<style lang="scss" scoped>
#mediation_request {
  min-width: 50vw;
  max-width: 750px;
}
</style>
