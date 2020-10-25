<template>
  <div class="bootstrap">
    <b-form
      @submit="showConfirmDialog"
    >
      <SingleSelectionVotingComponent
        v-if="poll.type===0"
        :options="shuffledOptions"
        @updateValidSelection="updateValidSelection"
        @updateVotingRequestValues="updateVotingRequestValues"
      />
      <MultiSelectionVotingComponent
        v-else-if="poll.type===1"
        :options="shuffledOptions"
        @updateValidSelection="updateValidSelection"
        @updateVotingRequestValues="updateVotingRequestValues"
      />
      <ThumbVotingComponent
        v-else-if="poll.type===2"
        :options="shuffledOptions"
        @updateValidSelection="updateValidSelection"
        @updateVotingRequestValues="updateVotingRequestValues"
      />
      <ScoreVotingComponent
        v-else-if="poll.type===3"
        :options="shuffledOptions"
        @updateValidSelection="updateValidSelection"
        @updateVotingRequestValues="updateVotingRequestValues"
      />

      <b-alert
        show
        variant="warning"
        class="mt-5"
      >
        {{ $i18n('poll.submit_vote_warning') }}
      </b-alert>
      <b-button
        v-if="mayVote"
        type="submit"
        variant="primary"
        :disabled="!isValidSelection"
      >
        {{ $i18n('poll.submit_vote') }}
      </b-button>
    </b-form>

    <b-modal
      ref="confirmModal"
      :title="$i18n('poll.submit_vote')"
      :cancel-title="$i18n('button.cancel')"
      :ok-title="$i18n('button.send')"
      modal-class="bootstrap"
      header-class="d-flex"
      content-class="pr-3 pt-3"
      @ok="submitVote"
    >
      {{ $i18n('poll.submit_vote_question') }}
    </b-modal>
  </div>
</template>

<script>
import { BButton, BForm, BAlert, BModal } from 'bootstrap-vue'
import ThumbVotingComponent from './ThumbVotingComponent'
import ScoreVotingComponent from './ScoreVotingComponent'
import SingleSelectionVotingComponent from './SingleSelectionVotingComponent'
import MultiSelectionVotingComponent from './MultiSelectionVotingComponent'
import { vote } from '@/api/voting'
import { pulseError, pulseSuccess, shuffle } from '@/script'
import i18n from '@/i18n'

export default {
  components: {
    ThumbVotingComponent,
    ScoreVotingComponent,
    SingleSelectionVotingComponent,
    MultiSelectionVotingComponent,
    BButton,
    BForm,
    BAlert,
    BModal,
  },
  props: {
    poll: {
      type: Object,
      required: true,
    },
    mayVote: {
      type: Boolean,
      default: false,
    },
  },
  data () {
    return {
      isValidSelection: false,
      votingRequestValues: null,
    }
  },
  computed: {
    shuffledOptions: function () {
      return shuffle(this.poll.options)
    },
  },
  methods: {
    showConfirmDialog (e) {
      e.preventDefault()
      this.$refs.confirmModal.show()
    },
    async submitVote (e) {
      this.isLoading = true
      this.isValidSelection = false
      try {
        await vote(this.poll.id, this.votingRequestValues)
        pulseSuccess(i18n('poll.vote_success'))
        this.$emit('disableVoteForm')
      } catch (e) {
        if (e.code === 403) {
          pulseError(i18n('poll.error_cannot_vote'))
        } else {
          pulseError(i18n('error_unexpected'))
        }
      }

      this.isLoading = false
    },
    updateValidSelection (value) {
      this.isValidSelection = value
    },
    updateVotingRequestValues (value) {
      this.votingRequestValues = value
    },
  },
}
</script>
