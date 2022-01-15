<template>
  <!-- eslint-disable vue/max-attributes-per-line -->
  <div id="report_request" class="popbox bootstrap m-2">
    <div
      v-if="!isReportButtonEnabled"
    >
      <div>
        <h3>{{ $i18n('profile.report.oldReportButton') }}</h3>
        <hr>
        <p>
          {{ $i18n('profile.report.oldReportButtonTextPart1') }} <br>
          {{ $i18n('profile.report.oldReportButtonTextPart2') }} <br>
        </p>
        <p>
          {{ $i18n('profile.report.oldReportButtonTextPart3') }}
          <a href="https://foodsharing.de/?page=blog&amp;sub=read&amp;id=255">{{ $i18n('profile.report.inthisblog') }}</a>
        </p>
      </div>
    </div>
    <b-alert
      v-else-if="!reporterHasReportGroup"
      variant="info" show
    >
      <div>
        {{ $i18n('profile.report.reporterHasNoReportGroup') }}
      </div>
    </b-alert>
    <b-alert
      v-else-if="isReportedIdReportAdmin && !hasArbitrationGroup && !isReporterIdReportAdmin"
      variant="info" show
    >
      <div>
        {{ $i18n('profile.report.reportedAdminNoArbitration', { name: foodSaverName }) }}
      </div>
    </b-alert>
    <b-alert
      v-else-if="isReporterIdReportAdmin && !hasArbitrationGroup"
      variant="info" show
    >
      <div>
        {{ $i18n('profile.report.reporterAdminNoArbitration') }}
      </div>
    </b-alert>
    <b-alert
      v-else-if="isReporterIdReportAdmin && isReportedIdArbitrationAdmin"
      variant="info" show
    >
      <div>
        {{ $i18n('profile.report.repAdminAgainstArbAdmin') }}
      </div>
    </b-alert>
    <b-alert
      v-else-if="isReporterIdArbitrationAdmin && isReportedIdReportAdmin"
      variant="info" show
    >
      <div>
        {{ $i18n('profile.report.arbAdminAgainstRepAdmin') }}
      </div>
    </b-alert>
    <b-alert
      v-else-if="!hasReportGroup"
      variant="info" show
    >
      <div>
        {{ $i18n('profile.report.noReportGroup') }}
      </div>
    </b-alert>
    <template
      v-else
    >
      <b-alert variant="info" show>
        <div>{{ $i18n('profile.report.info') }}</div>
      </b-alert>
      <b-form-select
        v-model="reportReason"
        :options="reportReasonOptions"
        class="mb-2"
        align-v="stretch"
      />
      <b-form-select
        v-if="storeListOptions.length > 1"
        v-model="storeList"
        :options="storeListOptions"
        class="mb-2"
        align-v="stretch"
      />
      <b-form-textarea
        v-model="reportText"
        class="mb-2"
        max-rows="8"
        size="sm"
      />
      <b-alert variant="info" show>
        <div>{{ $i18n('profile.report.mail') }}</div>
        <a :href="'mailto:' + emailAddress">{{ emailAddress }}</a>
      </b-alert>
      <b-button
        class="text-right"
        variant="secondary"
        size="sm"
        @click="trySendReport"
      >
        {{ $i18n('profile.report.send') }}
      </b-button>
    </template>
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
    storeListOptions: { type: Array, default: () => { return [] } },
    isReportedIdReportAdmin: { type: Boolean, required: true },
    hasReportGroup: { type: Boolean, required: true },
    hasArbitrationGroup: { type: Boolean, required: true },
    isReporterIdReportAdmin: { type: Boolean, required: true },
    isReportedIdArbitrationAdmin: { type: Boolean, required: true },
    isReporterIdArbitrationAdmin: { type: Boolean, required: true },
    isReportButtonEnabled: { type: Boolean, required: true },
    reporterHasReportGroup: { type: Boolean, required: true },
    mbName: { type: String, required: true },
  },
  data () {
    return {
      reportText: '',
      reportReason: null,
      storeList: null,
      reportReasonOptions: [
        { value: null, text: this.$i18n('profile.report.kindofreport') },
        { value: '1', text: this.$i18n('profile.report.late') },
        { value: '2', text: this.$i18n('profile.report.noshow') },
        { value: '10', text: this.$i18n('profile.report.cancellation') },
        { value: '15', text: this.$i18n('profile.report.sells') },
      ],
    }
  },
  computed: {
    emailAddress () {
      return this.mbName + '@foodsharing.network'
    },
  },
  methods: {
    async trySendReport () {
      const reportReasonText = this.reportReasonOptions.find(reportReasonOptions => reportReasonOptions.value === this.reportReason)
      const message = this.reportText.trim()
      if (!message) return
      try {
        await addReport(this.reportedId, this.reporterId, this.reportReason, reportReasonText.text, message, this.storeList)
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
  max-width: 550px;
}
</style>
