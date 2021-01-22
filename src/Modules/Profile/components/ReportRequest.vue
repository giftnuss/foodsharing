<template>
  <!-- eslint-disable vue/max-attributes-per-line -->
  <div id="report_request" class="popbox bootstrap m-2">
    <h3>
      {{ $i18n('profile.report.title', { name: foodSaverName }) }}
    </h3>
    <div>{{ $i18n('profile.report.info') }}</div>
    <b-form-select
      v-model="selected"
      :options="options"
    />
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
import $ from 'jquery'

import { addReport } from '@/api/report'
import { pulseError, pulseInfo } from '@/script'
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
      selected: null,
      options: [
        { value: null, text: 'Bitte wähle die Art der Meldung' },
        { value: '1', text: 'Ist zu spät gekommen' },
        { value: '2', text: 'Ist nicht zum abholen erschienen' },
      ],
    }
  },

  methods: {
    async trySendReport () {
      const selText = this.options.find(option => option.value === this.selected)
      const message = this.reportText.trim()
      if (!message) return
      try {
        await addReport(this.reportedId, this.reporterId, this.selected, selText.text, message)
        pulseInfo(i18n('profile.report.sent'))
        this.reportText = ''
        $.fancybox.close()
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
