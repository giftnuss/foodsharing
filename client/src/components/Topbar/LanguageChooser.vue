<template>
  <b-modal
    ref="languageChooserModal"
    :title="$i18n('language_chooser.title')"
    :cancel-title="$i18n('button.cancel')"
    :ok-title="$i18n('button.send')"
    modal-class="bootstrap"
    header-class="d-flex"
    content-class="pr-3 pt-3"
    @ok="changeLanguage"
  >
    {{ $i18n('language_chooser.content') }}
    <div
      v-if="loading"
      class="loader-container mx-auto"
    >
      <b-img
        center
        src="/img/469.gif"
      />
    </div>
    <b-form-select
      v-else
      v-model="language"
      :options="languages"
      text="Dropdown Button"
      class="m-md-2"
    />
  </b-modal>
</template>

<script>
import { BModal, BFormSelect, BImg } from 'bootstrap-vue'
import { pulseError } from '@/script'
import i18n from '@/i18n'
import { getLocale, setLocale } from '@/api/locale'

export default {
  name: 'LanguageChooser',
  components: { BModal, BFormSelect, BImg },
  data () {
    return {
      language: null,
      languages: [
        { value: 'de', text: 'Deutsch' },
        { value: 'en', text: 'English' },
        { value: 'fr', text: 'Français' },
        { value: 'it', text: 'Italiano' },
        { value: 'nb_NO', text: 'Norsk (Bokmål)' },
      ],
      loading: true,
    }
  },
  methods: {
    show () {
      this.$refs.languageChooserModal.show()
      this.getLanguage()
    },
    async getLanguage () {
      this.loading = true

      try {
        this.language = await getLocale()
      } catch (e) {
        pulseError(i18n('error_unexpected'))
      }

      this.loading = false
    },
    async changeLanguage () {
      try {
        await setLocale(this.language)
        location.reload()
      } catch (e) {
        pulseError(i18n('error_unexpected'))
      }
    },
  },
}
</script>

<style scoped>

</style>
