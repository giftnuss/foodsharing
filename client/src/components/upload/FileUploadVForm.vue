<template>
  <div
    id="input-wrapper"
    class="input-wrapper"
  >
    <label class="wrapper-label ui-widget">{{ $i18n('image') }}</label>

    <div class="element-wrapper">
      <file-upload
        :value="value"
        :image="image"
        :resize="resize"
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
    image: {
      type: Boolean
    },
    initialValue: {
      type: String,
      default: null
    },
    inputName: {
      type: String,
      default: 'file'
    },
    resize: {
      type: Array,
      default: null
    }
  },
  data () {
    return {
      value: this.initialValue
    }
  },
  methods: {
    onFileChange (file) {
      console.log(file)
      this.value = file.url
    }
  }
}
</script>

<style lang="scss">

</style>
