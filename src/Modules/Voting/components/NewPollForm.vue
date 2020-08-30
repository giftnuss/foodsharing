<template>
  <div class="bootstrap">
    <div class="card rounded">
      <div class="card-header text-white bg-primary">
        {{ $i18n('poll.new_poll.title') }} in {{ region.name }}
      </div>
      <b-form
        :class="{disabledLoading: isLoading, 'card-body': true}"
        @submit="submitPoll"
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
          :label="$i18n('poll.new_poll.scope')"
          class="mb-3"
        >
          <b-form-radio
            v-for="index in 5"
            :key="index"
            v-model="scope"
            :value="index - 1"
          >
            {{ $i18n('poll.scope_description_' + index) }}
          </b-form-radio>
        </b-form-group>

        <b-form-group
          :label="$i18n('poll.new_poll.type')"
          class="mb-4"
        >
          <b-form-radio
            v-for="index in 4"
            :key="index"
            v-model="type"
            :value="index - 1"
          >
            {{ $i18n('poll.type_description_' + (index - 1)) }}
          </b-form-radio>
        </b-form-group>

        <b-form-group class="mb-3">
          <b-form-row>
            <b-col>
              <label for="input-startdate">{{ $i18n('poll.new_poll.start_date') }}</label>
            </b-col>
            <b-col class="text-center">
              <label for="input-startdatetime">{{ $i18n('poll.new_poll.time') }}</label>
            </b-col>
          </b-form-row>
          <b-form-row class="ml-1">
            <b-col>
              <b-form-datepicker
                id="input-startdate"
                v-model="startDate"
                class="mb-2"
                :placeholder="$i18n('poll.new_poll.pick_a_date')"
                :min="new Date()"
                :state="$v.startDateTime.$error ? false : null"
                @input="updateDateTimes"
              />
            </b-col>
            <b-col>
              <b-form-timepicker
                id="input-startdatetime"
                v-model="startTime"
                :placeholder="$i18n('poll.new_poll.pick_a_time')"
                :state="$v.startDateTime.$error ? false : null"
                @input="updateDateTimes"
              />
            </b-col>
          </b-form-row>
          <div
            v-if="$v.startDateTime.$error"
            class="invalid-feedback"
          >
            {{ $i18n('poll.new_poll.start_date_required') }}
          </div>
        </b-form-group>
        <b-form-group
          :label="$i18n('poll.new_poll.end_date')"
          class="mb-3"
        >
          <b-form-row class="ml-2">
            <b-col>
              <b-form-datepicker
                id="input-enddate"
                v-model="endDate"
                class="mb-2"
                :placeholder="$i18n('poll.new_poll.pick_a_date')"
                :min="startDate"
                :state="$v.endDateTime.$error ? false : null"
                @input="updateDateTimes"
              />
            </b-col>
            <b-col>
              <b-form-timepicker
                id="input-enddatetime"
                v-model="endTime"
                :placeholder="$i18n('poll.new_poll.pick_a_time')"
                :state="$v.endDateTime.$error ? false : null"
                @input="updateDateTimes"
              />
            </b-col>
          </b-form-row>
          <div
            v-if="$v.endDateTime.$error"
            class="invalid-feedback"
          >
            {{ $i18n('poll.new_poll.end_date_required') }}
          </div>
        </b-form-group>

        <b-form-group
          :label="$i18n('poll.new_poll.description')"
          class="mb-4"
        >
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
      </b-form>
    </div>
  </div>
</template>

<script>

import { parse } from 'date-fns'
import {
  BForm,
  BFormGroup,
  BFormInput,
  BFormRadio,
  BFormDatepicker,
  BFormTimepicker,
  BFormTextarea,
  BFormSpinbutton,
  BButton,
  BFormRow,
  BCol
} from 'bootstrap-vue'
import { createPoll } from '@/api/voting'
import { pulseError } from '@/script'
import i18n from '@/i18n'
import { required, minLength } from 'vuelidate/lib/validators'

function isAfterStart (dateTime) {
  return dateTime > this.startDateTime
}

export default {
  components: {
    BForm,
    BFormGroup,
    BFormInput,
    BFormRadio,
    BFormDatepicker,
    BFormTimepicker,
    BFormTextarea,
    BFormSpinbutton,
    BButton,
    BFormRow,
    BCol
  },
  props: {
    region: {
      type: Object,
      required: true
    }
  },
  data () {
    return {
      isLoading: false,
      name: '',
      scope: 0,
      type: 0,
      startDate: null,
      startTime: null,
      endDate: null,
      endTime: null,
      description: '',
      numOptions: 3,
      options: Array(3).fill('')
    }
  },
  validations: {
    name: { required, minLength: minLength(1) },
    description: { required, minLength: minLength(1) },
    options: {
      required,
      $each: {
        required,
        minLength: minLength(1)
      }
    },
    startDateTime: { required },
    endDateTime: { required, isAfterStart }
  },
  computed: {
    startDateTime () {
      return parse(this.startDate + ' ' + this.startTime, 'yyyy-MM-dd HH:mm:ss', new Date())
    },
    endDateTime () {
      return parse(this.endDate + ' ' + this.endTime, 'yyyy-MM-dd HH:mm:ss', new Date())
    }
  },
  methods: {
    updateDateTimes () {
      this.$v.endDateTime.$touch()
      this.$v.startDateTime.$touch()
    },
    updateNumOptions () {
      // keeps the length of options in sync for the validation
      const oldLength = this.options.length
      this.options.length = this.numOptions
      if (this.numOptions > oldLength) {
        this.options.fill('', oldLength, this.numOptions)
      }
      this.$v.options.$touch()
    },
    async submitPoll (e) {
      e.preventDefault()
      this.isLoading = true
      try {
        const poll = await createPoll(this.region.id, this.name, this.description, this.startDateTime, this.endDateTime, this.scope, this.type, this.options, true)
        window.location = this.$url('poll', poll.id)
      } catch (e) {
        pulseError(i18n('error_unexpected') + ': ' + e.message)
      }

      this.isLoading = false
    }
  }
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
