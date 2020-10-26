<template>
  <div
    id="input-wrapper"
    class="input-wrapper"
  >
    <label class="wrapper-label ui-widget">{{ $i18n('image') }}</label>

    <div class="element-wrapper">
      <file-upload
        :filename="value"
        :is-image="isImage"
        :img-width="imgHeight"
        :img-height="imgWidth"
        @change="onFileChange"
      />
      <input
        :name="inputName"
        :value="value"
        type="hidden"
      >
    </div>
  </div>
</template>

<script>
import FileUpload from './FileUpload'

export default {
  components: { FileUpload },
  props: {
    isImage: {
      type: Boolean,
    },
    initialValue: {
      type: String,
      default: null,
    },
    inputName: {
      type: String,
      default: 'file',
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
    },
  },
}
</script>

<style lang="scss">

</style>
