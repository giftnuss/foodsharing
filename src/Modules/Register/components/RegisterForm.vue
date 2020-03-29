<template>
  <div class="bootstrap">
    <div class="card rounded">
      <div class="card-header text-white bg-primary">
        {{ $i18n('register.title') }} ({{ page }} / 6)
      </div>
      <div :class="{disabledLoading: isLoading, 'card-body': true}">
        <RegisterMailAndPassword
          v-if="page === 1"
          id="step1"
          :email.sync="email"
          :password.sync="password"
          @next="next()"
        />

        <RegisterName
          v-else-if="page === 2"
          id="step2"
          :firstname.sync="firstname"
          :lastname.sync="lastname"
          :gender.sync="gender"
          @prev="prev()"
          @next="next()"
        />
        <RegisterBirthdate
          v-else-if="page === 3"
          id="step3"
          :birthdate.sync="birthdate"
          @prev="prev()"
          @next="next()"
        />
        <RegisterMobilephone
          v-else-if="page === 4"
          id="step4"
          :mobile.sync="mobile"
          @prev="prev()"
          @next="next()"
        />
        <RegisterLegalAgreement
          v-else-if="page === 5"
          id="step5"
          :subscribe-newsletter.sync="subscribeNewsletter"
          :accept-gdpr.sync="acceptGdpr"
          :accept-legal.sync="acceptedLegal"
          @prev="prev()"
          @submit="submit()"
        />
        <RegisterSuccess
          v-if="page === 6"
          id="step6"
          @loadLogin="loadLogin()"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { register } from '@/api/foodsaver'
import { pulseSuccess, pulseError } from '@/script'
import i18n from '@/i18n'
import RegisterMailAndPassword from './RegisterMailAndPassword'
import RegisterName from './RegisterName'
import RegisterBirthdate from './RegisterBirthdate'
import RegisterMobilephone from './RegisterMobilephone'
import RegisterLegalAgreement from './RegisterLegalAgreement'
import RegisterSuccess from './RegisterSuccess'

export default {
  components: {
    RegisterMailAndPassword,
    RegisterName,
    RegisterBirthdate,
    RegisterMobilephone,
    RegisterLegalAgreement,
    RegisterSuccess
  },
  data () {
    return {
      page: 1,
      isLoading: false,
      password: '',
      email: '',
      firstname: '',
      lastname: '',
      gender: null,
      birthdate: null,
      mobile: '',
      subscribeNewsletter: true,
      acceptGdpr: false,
      acceptedLegal: false
    }
  },
  methods: {
    prev () {
      this.page--
    },
    next () {
      this.page++
    },
    loadLogin () {
      window.location = this.$url('login')
    },
    async submit () {
      this.isLoading = true

      try {
        await register({
          firstname: this.firstname,
          lastname: this.lastname,
          email: this.email,
          password: this.password,
          birthdate: this.birthdate,
          mobile: this.mobile,
          gender: this.gender,
          subscribeNewsletter: this.subscribeNewsletter ? 1 : 0
        })
        this.page = 6
        pulseSuccess(i18n('register.join_success'))
      } catch (err) {
        pulseError(`${i18n('register.join_error')}<br><br> ${err.message}`)
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

.bootstrap .form-control {
  background-color: var(--fs-white);
}
</style>

<style lang="scss" scoped>

.bootstrap .invalid-feedback {
  font-size: 100%;
  display: unset;
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
