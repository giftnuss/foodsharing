<template>
  <div class="bootstrap">
    <div class="card rounded">
      <div class="card-header text-white bg-primary">
        {{ $i18n('register.title') }} ({{ page }} / 6)
      </div>
      <div :class="{disabledLoading: isLoading, 'card-body': true}">
        <div
          v-if="page === 1"
          id="step1"
        >
          <div class="my-1">
            <div class="col-sm-9">
              <div class="msg-inside info">
                <i class="fas fa-info-circle" /> {{ $i18n('register.mail_hint') }}
              </div>
              <label for="email">{{ $i18n('register.login_email') }}</label><sup><i class="fas fa-asterisk" /></sup>
            </div> <div class="col-sm-9">
              <input
                id="email"
                v-model="$v.form1.email.$model"
                :class="{ 'is-invalid': $v.form1.email.$error }"
                type="email"
                name="email"
                class="form-control"
              >
              <div
                v-if="$v.form1.email.$error"
                class="invalid-feedback"
              >
                <span v-if="!$v.form1.email.required">{{ $i18n('register.email_required') }}</span>
                <span v-if="!$v.form1.email.email">{{ $i18n('register.email_invalid') }}</span>
              </div>
            </div>
            <div class="my-2">
              <div class="col-sm-3">
                <label for="password">{{ $i18n('register.login_passwd1') }}<sup><i class="fas fa-asterisk" /></sup></label>
              </div> <div class="col-sm-9">
                <input
                  id="password"
                  v-model="$v.form1.password.$model"
                  :class="{ 'is-invalid': $v.form1.password.$error }"
                  type="password"
                  name="password"
                  class="form-control"
                >
                <div
                  v-if="$v.form1.password.$error"
                  class="invalid-feedback"
                >
                  <span v-if="!$v.form1.password.required">{{ $i18n('register.password_required') }}</span>
                  <span v-if="!$v.form1.password.minLength">{{ $i18n('register.password_minLength') }}</span>
                </div>
              </div>
              <div class="my-1">
                <div class="col-sm-3">
                  <label for="confirmPassword">{{ $i18n('register.login_passwd2') }}<sup><i class="fas fa-asterisk" /></sup></label>
                </div> <div class="col-sm-9">
                  <input
                    id="confirmPassword"
                    v-model="$v.form1.confirmPassword.$model"
                    :class="{ 'is-invalid': $v.form1.confirmPassword.$error }"
                    type="password"
                    name="confirmPassword"
                    class="form-control"
                  >
                  <div
                    v-if="$v.form1.confirmPassword.$error"
                    class="invalid-feedback"
                  >
                    <span v-if="!$v.form1.confirmPassword.required">{{ $i18n('register.confirmPassword_required') }}</span>
                    <span v-else-if="!$v.form1.confirmPassword.sameAsPassword">{{ $i18n('register.confirmPassword_sameAsPassword') }}</span>
                  </div>
                </div>
                <button
                  :disabled="$v.form1.$invalid"
                  class="btn btn-secondary ml-3 mt-3"
                  type="submit"
                  @click="page = 2"
                >
                  {{ $i18n('register.next') }}
                </button>
                <span class="d-flex flex-row-reverse">{{ $i18n('register.requiredFields') }}<sup><i class="fas fa-asterisk" /></sup></span>
              </div>
            </div>
          </div>
        </div>

        <div
          v-else-if="page === 2"
          id="step2"
        >
          <div class="my-1">
            <div class="col-sm-9">
              <label>{{ $i18n('register.select_your_gender') }}<sup><i class="fas fa-asterisk" /></sup></label>
            </div>
            <div class="col-sm-9">
              <b-form-group id="genderFormGroup">
                <b-form-radio-group
                  id="genderRadioGroup"
                  v-model="form2.gender"
                  name="gender"
                >
                  <b-form-radio
                    id="genderWoman"
                    value="1"
                  >
                    {{ $i18n('register.woman') }}
                  </b-form-radio>
                  <b-form-radio
                    id="genderMan"
                    value="2"
                  >
                    {{ $i18n('register.man') }}
                  </b-form-radio>
                  <b-form-radio
                    id="genderOther"
                    value="3"
                  >
                    {{ $i18n('register.other') }}
                  </b-form-radio>
                </b-form-radio-group>
              </b-form-group>
            </div>
            <div class="my-1">
              <div class="col-sm-9">
                <label for="firstname">{{ $i18n('register.login_name') }}<sup><i class="fas fa-asterisk" /></sup></label>
              </div> <div class="col-sm-9">
                <input
                  id="firstname"
                  v-model="$v.form2.firstname.$model"
                  :class="{ 'is-invalid': $v.form2.firstname.$error }"
                  type="text"
                  name="firstname"
                  class="form-control"
                >
                <div
                  v-if="$v.form2.firstname.$error"
                  class="invalid-feedback"
                >
                  <span v-if="!$v.form2.firstname.required">{{ $i18n('register.firstname_required') }}</span>
                  <span v-if="!$v.form2.firstname.minLength">{{ $i18n('register.firstname_minLength') }}</span>
                </div>
              </div>
              <div class="my-1">
                <div class="col-sm-9">
                  <label for="lastname">{{ $i18n('register.login_surname') }}<sup><i class="fas fa-asterisk" /></sup></label>
                </div> <div class="col-sm-9">
                  <input
                    id="lastname"
                    v-model="$v.form2.lastname.$model"
                    :class="{ 'is-invalid': $v.form2.lastname.$error }"
                    type="text"
                    name="lastname"
                    class="form-control"
                  >
                  <div
                    v-if="$v.form2.lastname.$error"
                    class="invalid-feedback"
                  >
                    <span v-if="!$v.form2.lastname.required">{{ $i18n('register.lastname_required') }}</span>
                    <span v-if="!$v.form2.lastname.minLength">{{ $i18n('register.lastname_minLength') }}</span>
                  </div>
                </div>
              </div>
              <button
                class="btn btn-secondary ml-3 mt-3"
                @click="page = 1"
              >
                {{ $i18n('register.prev') }}
              </button>
              <button
                :disabled="$v.form2.$invalid"
                class="btn btn-secondary mt-3"
                @click="page = 3"
              >
                {{ $i18n('register.next') }}
              </button>
              <span class="d-flex flex-row-reverse">{{ $i18n('register.requiredFields') }}<sup><i class="fas fa-asterisk" /></sup></span>
            </div>
          </div>
        </div>
        <div
          v-else-if="page === 3"
          id="step3"
        >
          <div class="my-1">
            <div class="col-sm-9">
              <label for="birthdate">{{ $i18n('register.geb_datum') }}<sup><i class="fas fa-asterisk" /></sup></label>
            </div> <div class="mt-2 col-sm-9">
              <div
                v-if="$v.form3.birthdate.$error"
                class="invalid-feedback"
              >
                <span v-if="$v.form3.$invalid">{{ $i18n('register.error_birthdate') }}</span>
              </div>
            </div><div class="mt-2 col-sm-9">
              <datepicker
                v-model="$v.form3.birthdate.$model"
                :bootstrap-styling="true"
                :language="de"
                :typeable="true"
                :value="state.date"
                :calendar-button="true"
                :format="customFormatter"
                :class="{ 'is-invalid': $v.form3.birthdate.$error }"
                calendar-button-icon="fa fa-calendar"
                placeholder="2001-03-25 (JJJJ-MM-DD)"
              />
            </div>
            <div class="mt-3 col-sm-9">
              <div class="msg-inside info">
                <i class="fas fa-info-circle" /> {{ $i18n('register.birthdate_hint') }}
              </div>
            </div>
            <button
              class="btn btn-secondary ml-3 mt-3"
              @click="page = 2"
            >
              {{ $i18n('register.prev') }}
            </button>
            <button
              :disabled="$v.form3.$invalid"
              class="btn btn-secondary mt-3"
              @click="page = 4"
            >
              {{ $i18n('register.next') }}
            </button>
            <span class="d-flex flex-row-reverse">{{ $i18n('register.requiredFields') }}<sup><i class="fas fa-asterisk" /></sup></span>
          </div>
        </div>
        <div
          v-else-if="page === 4"
          id="step4"
        >
          <div class="my-1">
            <div class="col-sm-3">
              <label for="mobile">{{ $i18n('register.login_mobile_phone') }}</label>
            </div> <div class="col-sm-9">
              <input
                id="form4.mobile"
                v-model="form4.mobile"
                type="tel"
                name="mobile"
                class="form-control"
                pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}"
                size="16"
                placeholder="+4917123456789"
              >
            </div><div class="mt-3 col-sm-9">
              <div class="msg-inside info">
                <i class="fas fa-info-circle" /> {{ $i18n('register.login_phone_info') }}
              </div>
            </div>
            <div class="col-sm-9">
              <button
                class="btn btn-secondary mt-3"
                @click="page = 3"
              >
                {{ $i18n('register.prev') }}
              </button>
              <button
                class="btn btn-secondary mt-3"
                @click="page = 5"
              >
                {{ $i18n('register.next') }}
              </button>
            </div>
          </div>
        </div>
        <div
          v-else-if="page === 5"
          id="step5"
        >
          <div class="my-1">
            <b-form-textarea
              id="textarea-plaintext"
              :value="legal"
              readonly
              rows="20"
            />

            <div
              :class="{invalid: $v.form5.joinLegal1.$invalid}"
              class="form-check pt-3"
            >
              <input
                id="form5.join_legal1"
                v-model="form5.joinLegal1"
                class="form-check-input"
                type="checkbox"
                value=""
                @change="$v.form5.joinLegal1.$touch()"
              >
              <label
                class="form-check-label"
                for="join_legal1"
              />
              {{ $i18n('register.have_read_the_legal_stuff1') }}

              <a
                href="https://foodsharing.de/?page=legal"
                target="_blank"
                rel="noopener noreferrer nofollow"
              >{{ $i18n('legal.pp') }}</a> {{ $i18n('register.have_read_the_legal_stuff2') }}
            </div>
            <div
              :class="{invalid: $v.form5.joinLegal2.$invalid}"
              class="form-check"
            >
              <input
                id="form5.join_legal2"
                v-model="form5.joinLegal2"
                class="form-check-input"
                type="checkbox"
                value=""
                @change="$v.form5.joinLegal2.$touch()"
              >
              <label
                class="form-check-label"
                for="join_legal2"
              />
              {{ $i18n('register.have_read_the_legal_stuff1') }}

              <a
                href="https://wiki.foodsharing.de/Rechtsvereinbarung"
                target="_blank"
                rel="noopener noreferrer nofollow"
              >{{ $i18n('legal.legal_agreement') }}</a> {{ $i18n('register.have_read_the_legal_stuff2') }}
            </div>
            <b-form-checkbox
              id="form5.subscribeNewsletter"
              v-model="form5.subscribeNewsletter"
              name="subscribeNewsletter"
              value="1"
              unchecked-value="0"
            >
              {{ $i18n('register.signup_newsletter') }}
            </b-form-checkbox>
          </div>
          <button
            class="btn btn-secondary ml-3 mt-3"
            @click="page = 4"
          >
            {{ $i18n('register.prev') }}
          </button>
          <button
            :disabled="$v.form5.$invalid"
            class="btn btn-secondary mt-3"
            @click="submit"
          >
            {{ $i18n('register.finish') }}
          </button>
        </div>

        <div
          v-if="page === 6"
          id="step6"
        >
          <div class="my-1">
            <div class="col-sm-9">
              <div class="msg-inside info">
                <i class="fas fa-info-circle" /> {{ $i18n('register.join_success_message') }}
              </div>
              <b-button
                href="?page=login"
                class="btn btn-secondary ml-3 mt-3"
              >
                {{ $i18n('login.login_button_label') }}
              </b-button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { register } from '@/api/foodsaver'
import { pulseSuccess, pulseError } from '@/script'
import i18n from '@/i18n'
import { required, email, minLength, sameAs } from 'vuelidate/lib/validators'
import Datepicker from 'vuejs-datepicker'
import { de } from 'vuejs-datepicker/dist/locale'
import { ageCheck, dateValid } from '../customValidation'
import { format } from 'date-fns'

export default {
  components: {
    Datepicker
  },
  props: {
    legal: {
      type: String,
      default: 'Default legal'
    },
    state: {
      type: Date,
      date: new Date(),
      default: new Date()
    }
  },
  data () {
    return {
      page: 1,
      isLoading: false,
      submitted: false,
      selectedDate: '',
      de: de,
      form1: {
        password: '',
        confirmPassword: '',
        email: ''
      },
      form2: {
        firstname: '',
        lastname: '',
        gender: null
      },
      form3: {
        birthdate: ''
      },
      form4: {
        mobile: ''
      },
      form5: {
        subscribeNewsletter: 1
      }
    }
  },
  validations: {
    form1: {
      email: { required, email },
      password: { required, minLength: minLength(8) },
      confirmPassword: { required, sameAsPassword: sameAs('password') }
    },
    form2: {
      firstname: { required, minLength: minLength(2) },
      lastname: { required, minLength: minLength(2) },
      gender: { required }
    },
    form3: {
      birthdate: {
        required,
        dateValid,
        ageCheck
      }
    },
    form5: {
      joinLegal1: { sameAs: sameAs(() => true) },
      joinLegal2: { sameAs: sameAs(() => true) }
    }
  },
  methods: {
    customFormatter (birthdate) {
      return format(new Date(birthdate), 'YYYY-MM-DD')
    },
    async submit () {
      this.isLoading = true

      this.$v.$touch()
      if (this.$v.$anyError) {
        return
      }

      try {
        await register({
          firstname: this.form2.firstname,
          lastname: this.form2.lastname,
          email: this.form1.email,
          password: this.form1.password,
          birthdate: this.form3.birthdate,
          mobile: this.form4.mobile,
          gender: this.form2.gender,
          subscribeNewsletter: this.form5.subscribeNewsletter
        })
        this.page = 6
        pulseSuccess(i18n('register.join_success'))
      } catch (err) {
        pulseError(i18n('register.join_error'))
      }
      this.isLoading = false
    }
  }
}
</script>

<style>
.bootstrap .form-inline {
  display: none;
}

div#main {
  margin-top: 45px;
}
</style>

<style lang="scss" scoped>

.bootstrap .invalid-feedback {
  font-size: 100%;
  display: unset;
}

.bootstrap .form-control {
  background-color: var(--fs-white);
}

.bootstrap .input-group input.form-control {
  padding-left: 12px;
}

.bootstrap .input-group .input-group-text {
  background-color: var(--fs-beige);
}

.vdp-datepicker__calendar {
  margin-left: -22px;
}

.bootstrap .form-control {
    background-color: var(--fs-white);
}

.bootstrap .input-group .input-group-text {
    background-color: var(--fs-beige);
    padding-left: 12px;

}
</style>
