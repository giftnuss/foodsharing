<template>
  <div class="bootstrap">
    <div class="card rounded">
      <div class="card-header text-white bg-primary">
        {{ $i18n('poll.new_poll.title') }} in {{ region.name }}
      </div>
      <b-form
        class="card-body"
        @submit="submitPoll"
      >
        <b-form-group
          :label="$i18n('poll.new_poll.name')"
          label-for="input-name"
          :state="isNameValid"
        >
          <b-form-input
            id="input-name"
            v-model="name"
            trim
          />
        </b-form-group>
        <div>{{ name }}</div>

        <b-form-group
          :label="$i18n('poll.new_poll.scope')"
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
        >
          <b-form-radio
            v-for="index in 4"
            :key="index"
            v-model="type"
            :value="index - 1"
          >
            {{ $i18n('poll.type_description_' + index) }}
          </b-form-radio>
        </b-form-group>

        <label for="input-startdate">{{ $i18n('poll.new_poll.start_date') }}</label>
        <div class="row ml-2">
          <b-form-datepicker
            id="input-startdate"
            v-model="startDate"
            class="mb-2 col"
          />
          <b-time
            id="input-startdate-time"
            v-model="startTime"
            class="col"
            hide-header
          />
        </div>

        <label for="input-enddate">{{ $i18n('poll.new_poll.end_date') }}</label>
        <div class="row ml-2">
          <b-form-datepicker
            id="input-enddate"
            v-model="endDate"
            class="mb-2 col"
          />
          <b-time
            id="input-enddate-time"
            v-model="endTime"
            class="col"
            hide-header
          />
        </div>

        <label for="input-description">{{ $i18n('poll.new_poll.description') }}</label>
        <b-form-textarea
          id="input-description"
          v-model="description"
          :placeholder="$i18n('poll.new_poll.description_placeholder')"
          rows="3"
          max-rows="6"
        />

        <b-form-group
          :label="$i18n('poll.new_poll.options')"
          label-for="input-name"
          :state="isNameValid"
        >
          <b-form-spinbutton
            id="input-num-options"
            v-model="numOptions"
            min="1"
            max="10"
          />

          <div
            v-for="index in numOptions"
            :key="index"
            class="row"
          >
            <div
              class="col"
            >
              Option {{ index }}:
            </div>
            <b-form-input
              id="input-option-0"
              v-model="options[index-1]"
              trim
              class="col"
            />
          </div>
        </b-form-group>
        <div>{{ options }}</div>

        <b-button
          type="submit"
          variant="primary"
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
  BTime,
  BFormTextarea,
  BFormSpinbutton,
  BButton
} from 'bootstrap-vue'
import { createPoll } from '@/api/voting'
import { pulseError } from '@/script'
import i18n from '@/i18n'

export default {
  components: {
    BForm,
    BFormGroup,
    BFormInput,
    BFormRadio,
    BFormDatepicker,
    BTime,
    BFormTextarea,
    BFormSpinbutton,
    BButton
  },
  props: {
    region: {
      type: Object,
      required: true
    }
  },
  data () {
    return {
      name: '',
      scope: 0,
      type: 0,
      startDate: null,
      startTime: null,
      endDate: null,
      endTime: null,
      description: '',
      numOptions: 3,
      options: []
    }
  },
  computed: {
    isNameValid () {
      return this.name.length > 0
    },
    startDateTime () {
      return parse(this.startDate + ' ' + this.startTime, 'yyyy-MM-dd HH:mm:ss', new Date())
    },
    endDateTime () {
      return parse(this.endDate + ' ' + this.endTime, 'yyyy-MM-dd HH:mm:ss', new Date())
    }
  },
  methods: {
    async submitPoll (e) {
      e.preventDefault()
      this.isLoading = true
      this.isValidSelection = false
      try {
        const poll = await createPoll(this.region.id, this.name, this.description, this.startDateTime, this.endDateTime, this.scope, this.type, this.options, true)
        window.location = this.$url('poll', poll.id)
      } catch (e) {
        pulseError(i18n('error_unexpected'))
      }

      this.isLoading = false
    }
  }
}
</script>

<style>
#input-num-options {
  width: 200px
}
</style>
