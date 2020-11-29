<template>
  <div class="bootstrap">
    <div class="card rounded">
      <div class="card-header text-white bg-primary">
        {{ $i18n('poll.new_poll.title') }}
      </div>
      <b-form
        :class="{disabledLoading: isLoading, 'card-body': true}"
        @submit="showConfirmDialog"
      >
        <b-form-group
          :label="$i18n('poll.new_poll.name')"
          label-for="input-name"
          class="mb-4"
        >
          <b-form-input
            id="input-name"
            v-model="$v.name.$model"
            trim
            :state="$v.name.$error ? false : null"
          />
          <div
            v-if="$v.name.$error"
            class="invalid-feedback"
          >
            {{ $i18n('poll.new_poll.name_required') }}
          </div>
        </b-form-group>

        <b-form-group
          :label="$i18n('poll.new_poll.description')"
          class="mb-4"
        >
          <div
            class="mb-2 ml-2"
            v-html="$i18n('forum.markdown_description')"
          />
          <b-form-textarea
            id="input-description"
            v-model="$v.description.$model"
            :placeholder="$i18n('poll.new_poll.description_placeholder')"
            trim
            :state="$v.description.$error ? false : null"
            rows="5"
            class="ml-1"
          />
          <div
            v-if="$v.description.$error"
            class="invalid-feedback"
          >
            {{ $i18n('poll.new_poll.description_required') }}
          </div>
        </b-form-group>

        <b-form-group
          :label="$i18n('poll.new_poll.options')"
          label-for="input-name"
          class="mb-4"
        >
          <b-form-spinbutton
            id="input-num-options"
            v-model="numOptions"
            min="2"
            max="10"
            class="m-1 mb-3 mr-3"
            style="width:120px"
            size="sm"
            @input="updateNumOptions"
          />

          <b-form-row
            v-for="index in numOptions"
            :key="index"
            class="row"
          >
            <b-col
              cols="3"
              align-v="stretch"
            >
              {{ $i18n('poll.new_poll.option') }} {{ index }}:
            </b-col>
            <b-col>
              <b-form-input
                id="input-option-0"
                v-model="$v.options.$model[index-1]"
                trim
                :state="$v.options.$error ? false : null"
                class="mr-3 mb-1"
              />
            </b-col>
          </b-form-row>
          <div
            v-if="$v.options.$error"
            class="invalid-feedback"
          >
            {{ $i18n('poll.new_poll.option_texts_required') }}
          </div>
        </b-form-group>

        <b-button
          type="submit"
          variant="primary"
          :disabled="$v.$invalid"
        >
          {{ $i18n('poll.new_poll.submit') }}
        </b-button>
        <div
          v-if="$v.$invalid"
          class="invalid-feedback"
        >
          {{ $i18n('poll.new_poll.missing_fields') }}
        </div>
      </b-form>
    </div>

    <b-modal
      ref="editPollConfirmModal"
      :title="$i18n('poll.new_poll.submit')"
      :cancel-title="$i18n('button.cancel')"
      :ok-title="$i18n('button.send')"
      modal-class="bootstrap"
      header-class="d-flex"
      content-class="pr-3 pt-3"
      @ok="submitPoll"
    >
      {{ $i18n('poll.edit.submit_question') }}
    </b-modal>
  </div>
</template>

<script>

import {
  BForm,
  BFormGroup,
  BFormInput,
  BFormTextarea,
  BFormSpinbutton,
  BButton,
  BFormRow,
  BCol,
  BModal,
} from 'bootstrap-vue'
import { editPoll } from '@/api/voting'
import { pulseError } from '@/script'
import i18n from '@/i18n'
import { required, minLength } from 'vuelidate/lib/validators'

export default {
  components: {
    BForm,
    BFormGroup,
    BFormInput,
    BFormTextarea,
    BFormSpinbutton,
    BButton,
    BFormRow,
    BCol,
    BModal,
  },
  props: {
    poll: {
      type: Object,
      required: true,
    },
  },
  data () {
    return {
      isLoading: false,
      name: this.poll.name,
      description: this.poll.description,
      numOptions: this.poll.options.length,
      options: this.poll.options.map(x => x.text),
    }
  },
  validations: {
    name: { required, minLength: minLength(1) },
    description: { required, minLength: minLength(1) },
    options: {
      required,
      $each: {
        required,
        minLength: minLength(1),
      },
    },
  },
  methods: {
    updateNumOptions () {
      // keeps the length of options in sync for the validation
      const oldLength = this.options.length
      this.options.length = this.numOptions
      if (this.numOptions > oldLength) {
        this.options.fill('', oldLength, this.numOptions)
      }
      this.$v.options.$touch()
    },
    showConfirmDialog (e) {
      e.preventDefault()
      this.$refs.editPollConfirmModal.show()
    },
    async submitPoll (e) {
      e.preventDefault()
      this.isLoading = true
      try {
        await editPoll(this.poll.id, this.name, this.description, this.options)
        window.location = this.$url('poll', this.poll.id)
      } catch (e) {
        pulseError(i18n('error_unexpected') + ': ' + e.message)
      }

      this.isLoading = false
    },
  },
}
</script>

<style lang="scss" scoped>
#input-num-options {
  width: 120px;
}

.invalid-feedback {
  font-size: 100%;
  display: unset;
}
</style>
