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
          class="mb-4"
        >
          <b-form-input
            id="input-name"
            v-model="name"
            trim
          />
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

        <b-row>
          <b-col>
            <label for="input-startdate">{{ $i18n('poll.new_poll.start_date') }}</label>
          </b-col>
          <b-col class="text-center">
            <label for="input-startdate-time">Uhrzeit</label>
          </b-col>
        </b-row>
        <b-row class="ml-2 mb-2">
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
        </b-row>

        <label for="input-enddate">{{ $i18n('poll.new_poll.end_date') }}</label>
        <b-row class="ml-2 mb-3">
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
        </b-row>

        <label for="input-description">{{ $i18n('poll.new_poll.description') }}</label>
        <b-form-textarea
          id="input-description"
          v-model="description"
          :placeholder="$i18n('poll.new_poll.description_placeholder')"
          rows="3"
          max-rows="6"
          class="ml-1 mb-4"
        />

        <b-form-group
          :label="$i18n('poll.new_poll.options')"
          label-for="input-name"
          :state="isNameValid"
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
          />

          <b-row
            v-for="index in numOptions"
            :key="index"
            class="row"
          >
            <b-col
              cols="3"
              align-v="stretch"
            >
              Option {{ index }}:
            </b-col>
            <b-col>
              <b-form-input
                id="input-option-0"
                v-model="options[index-1]"
                trim
                class="mr-3 mb-1"
              />
            </b-col>
          </b-row>
        </b-form-group>

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
  BButton,
  BRow,
  BCol
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
    BButton,
    BRow,
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
        pulseError(e)
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
</style>
