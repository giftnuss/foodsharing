<template>
  <div
    id="input-wrapper"
    class="input-wrapper"
  >
    <label class="wrapper-label ui-widget">{{ $i18n('image') }}</label>

    <div class="element-wrapper">
      <file-upload
        :filename="value"
        :is-image="true"
        :img-width="imgHeight"
        :img-height="imgWidth"
        @change="onFileChange"
      />
    </div>
  </div>
</template>

<script>
import FileUpload from '@/components/upload/FileUpload'
import { setProfilePhoto } from '@/api/settings'
import { hideLoader, pulseError, showLoader } from '@/script'
import i18n from '@/i18n'

export default {
  components: { FileUpload },
  props: {
    initialValue: {
      type: String,
      default: null,
    },
    imgHeight: {
      type: Number,
      default: 0,
    },
    imgWidth: {
      type: Number,
      default: 0,
    },
  },
  data () {
    return {
      value: this.initialValue,
    }
  },
  methods: {
    onFileChange (file) {
      console.log(file)
      this.value = file.url

      showLoader()
      try {
        setProfilePhoto(file.uuid)
      } catch (e) {
        console.error(e)
        pulseError(i18n('error_unexpected'))
      }
      hideLoader()
    },
  },
}
</script>

<style lang="scss">

</style>
