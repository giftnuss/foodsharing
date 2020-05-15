<template>
  <form class="my-1">
    <div class="col-sm-auto">
      <label>{{ $i18n('register.select_your_gender') }}<sup><i class="fas fa-asterisk" /></sup></label>
    </div>
    <div class="col-sm-auto">
      <b-form-group id="genderFormGroup">
        <b-form-radio-group
          id="genderRadioGroup"
          :checked="gender"
          :state="$v.gender.$error ? false : null"
          :value="gender"
          name="gender"
          @input="$emit('update:gender', $event)"
        >
          <b-form-radio
            id="genderWoman"
            :value="1"
          >
            {{ $i18n('register.woman') }}
          </b-form-radio>
          <b-form-radio
            id="genderMan"
            :value="2"
          >
            {{ $i18n('register.man') }}
          </b-form-radio>
          <b-form-radio
            id="genderOther"
            :value="3"
          >
            {{ $i18n('register.other') }}
          </b-form-radio>
        </b-form-radio-group>
      </b-form-group>
    </div>
    <div class="my-1">
      <div class="col-sm-auto">
        <label for="firstname">{{ $i18n('register.login_name') }}<sup><i class="fas fa-asterisk" /></sup></label>
      </div> <div class="col-sm-auto">
        <input
          id="firstname"
          v-model.lazy="$v.firstname.$model"
          :class="{ 'is-invalid': $v.firstname.$error }"
          type="text"
          name="firstname"
          class="form-control"
          @input="$emit('update:firstname', $event.target.value)"
        >
        <div
          v-if="$v.firstname.$error"
          class="invalid-feedback"
        >
          <span v-if="!$v.firstname.required">{{ $i18n('register.firstname_required') }}</span>
          <span v-if="!$v.firstname.minLength">{{ $i18n('register.firstname_minLength') }}</span>
        </div>
      </div>
      <div class="my-1">
        <div class="col-sm-auto">
          <label for="lastname">{{ $i18n('register.login_surname') }}<sup><i class="fas fa-asterisk" /></sup></label>
        </div> <div class="col-sm-auto">
          <input
            id="lastname"
            v-model.lazy="$v.lastname.$model"
            :class="{ 'is-invalid': $v.lastname.$error }"
            type="text"
            name="lastname"
            class="form-control"
            @input="$emit('update:lastname', $event.target.value)"
          >
          <div
            v-if="$v.lastname.$error"
            class="invalid-feedback"
          >
            <span v-if="!$v.lastname.required">{{ $i18n('register.lastname_required') }}</span>
            <span v-if="!$v.lastname.minLength">{{ $i18n('register.lastname_minLength') }}</span>
          </div>
        </div>
      </div>
      <button
        class="btn btn-secondary ml-3 mt-3"
        type="button"
        @click.prevent="$emit('prev')"
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
    </div>
  </form>
</template>
<script>
import { required, minLength } from 'vuelidate/lib/validators'

export default {
  props: { firstname: { type: String, default: '' }, lastname: { type: String, default: '' }, gender: { type: Number, default: 1 } },

  validations: {
    firstname: { required, minLength: minLength(2) },
    lastname: { required, minLength: minLength(2) },
    gender: { required }
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
<style lang="scss" scoped>
.invalid-feedback {
  font-size: 100%;
  display: unset;
}
</style>
