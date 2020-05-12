<template>
  <form class="my-1">
    <div class="col-sm-auto">
      <label>{{ $i18n('register.geb_datum') }}<sup><i class="fas fa-asterisk" /></sup></label>
    </div>
    <div class="mt-2 col-sm-auto">
      <DatePicker
        :value="$v.birthdate.$model"
        format="dd.MM.yyyy"
        :bootstrap-styling="true"
        :language="de"
        open-date="2000-01-01"
        placeholder="25.03.2001 (DD.MM.JJJJ)"
        initial-view="year"
        :typeable="true"
        :show-calendar-on-button-click="true"
        :calendar-button="true"
        input-class="datepickerClass pl-3"
        :class="{ 'is-invalid': $v.birthdate.$error }"
        calendar-button-icon="fa fa-calendar"
        @selected="$emit('update:birthdate', $event)"
        @input="$emit('update:birthdate', $event)"
      />
      <div
        v-if="$v.birthdate.$error"
        class="invalid-feedback"
      >
        <span v-if="!$v.birthdate.ageCheck">{{ $i18n('register.error_birthdate') }}</span>
        <span v-if="!$v.birthdate.required">{{ $i18n('register.error_birthdate') }}</span>
      </div>
    </div>
    <div class="mt-3 col-sm-auto">
      <div class="msg-inside info">
        <i class="fas fa-info-circle" />
        <span v-html="$i18n('register.birthdate_hint')" />
      </div>
    </div>
    <button
      class="btn btn-secondary ml-3 mt-3"
      type="button"
      @click="$emit('prev')"
    >
      {{ $i18n('register.prev') }}
    </button>
    <button
      class="btn btn-secondary mt-3"
      type="submit"
      @click.prevent="redirect()"
    >
      {{ $i18n('register.next') }}
    </button>
    <span class="mr-3 d-flex flex-row-reverse">{{ $i18n('register.requiredFields') }}<sup><i class="fas fa-asterisk" /></sup></span>
  </form>
</template>

<script>
import DatePicker from '@sum.cumo/vue-datepicker'
import '@sum.cumo/vue-datepicker/dist/vuejs-datepicker.css'
import { de } from '@sum.cumo/vue-datepicker/dist/locale'
import { ageCheck, dateValid } from './birthdateValidation'
import { required } from 'vuelidate/lib/validators'

export default {
  components: {
    DatePicker
  },
  props: { birthdate: { type: Date, default: null } },
  data () {
    return {
      de: de
    }
  },
  validations: {
    birthdate: {
      required,
      dateValid,
      ageCheck
    }
  },
  methods: {
    redirect () {
      this.$v.$touch()
      if (!this.$v.$invalid) {
        this.$emit('next')
      }
    }
  }
}
</script>
<style>
.datepickerClass {
  border: 1px solid var(--border) !important;
}

.bootstrap .input-group .input-group-text {
  background-color: var(--fs-green);
  color: white;
}
</style>

<style lang="scss" scoped>
.invalid-feedback {
  font-size: 100%;
  display: unset;
}
</style>
