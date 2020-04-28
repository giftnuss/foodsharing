<template>
  <form class="my-1 ml-3 mt-3">
    <b-form-checkbox
      id="acceptGdpr"
      :checked="acceptGdpr"
      name="acceptGdpr"
      @change="$emit('update:acceptGdpr', $event)"
    >
      {{ $i18n('register.have_read_the_legal_stuff1') }}
      <a
        :href="$url('dataprivacy')"
        target="_blank"
        rel="noopener noreferrer nofollow"
      >{{ $i18n('legal.pp') }}</a> {{ $i18n('register.have_read_the_legal_stuff2') }}
    </b-form-checkbox>
    <b-form-checkbox
      id="acceptLegal"
      :checked="acceptLegal"
      name="acceptLegal"
      @input="$emit('update:acceptLegal', $event)"
    >
      {{ $i18n('register.have_read_the_legal_stuff1') }}
      <a
        :href="$url('legal_agreement')"
        target="_blank"
        rel="noopener noreferrer nofollow"
      >{{ $i18n('legal.legal_agreement') }}</a> {{ $i18n('register.have_read_the_legal_stuff2') }}
    </b-form-checkbox>
    <b-form-checkbox
      id="subscribeNewsletter"
      :checked="subscribeNewsletter"
      name="subscribeNewsletter"
      @input="$emit('update:subscribeNewsletter', $event)"
    >
      {{ $i18n('register.signup_newsletter') }}
    </b-form-checkbox>
    <button
      class="btn btn-secondary ml-3 mt-3"
      type="button"
      @click="$emit('prev')"
    >
      {{ $i18n('register.prev') }}
    </button>
    <button
      :disabled="$v.$invalid"
      type="submit"
      class="btn btn-secondary mt-3"
      @click.prevent="$emit('submit')"
    >
      {{ $i18n('register.finish') }}
    </button>
  </form>
</template>
<script>
import { sameAs } from 'vuelidate/lib/validators'

export default {
  props: { subscribeNewsletter: { type: Boolean, default: true }, acceptGdpr: { type: Boolean, default: false }, acceptLegal: { type: Boolean, default: false } },

  validations: {
    acceptGdpr: { sameAs: sameAs(() => true) },
    acceptLegal: { sameAs: sameAs(() => true) }
  }
}
</script>
