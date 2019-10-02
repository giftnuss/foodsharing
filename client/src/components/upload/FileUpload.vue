
<template>
  <div class="bootstrap ">
    <input
      ref="uploadElement"
      @change="onFileChange"
      :accept="accept"
      name="imagefile[]"
      class="d-none"
      type="file"
    >
    <div
      v-if="image"
      class="row align-items-center"
    >
      <div class="col-2">
        <div class="preview">
          <img
            v-if="value"
            :src="value+'?h=100&w=100'"
            alt="Preview Image"
          >
        </div>
      </div>
      <div class="col">
        <div v-if="value">
          {{ filename }}
        </div>
        <div
          v-else
          class="text-muted"
        >
          {{ $i18n('upload.no_image_yet') }}
        </div>
        <button
          @click.prevent="openUploadDialog"
          :class="`btn btn-sm btn-secondary ${isLoading ? 'disabledLoading' : ''}`"
        >
          <span v-if="value">{{ $i18n('upload.new_neuter') }} </span>{{ $i18n('upload.image') }}
        </button>
      </div>
    </div>
    <div v-else>
      <div v-if="value">
        {{ filename }}
      </div>
      <div
        v-else
        class="text-muted"
      >
        {{ $i18n('upload.no_image_chosen') }}
      </div>
      <button
        @click.prevent="openUploadDialog"
        :class="`btn btn-sm btn-secondary ${isLoading ? 'disabledLoading' : ''}`"
      >
        <span v-if="value">{{ $i18n('upload.new_feminine') }} </span>{{ $i18n('upload.file') }}
      </button>
    </div>

    <b-modal
      ref="upload-modal"
      :static="true"
      @ok="cropImage"
      size="lg"
      title="Bild ausschneiden"
      modal-class="bootstrap"
      hide-header-close
    >
      <div class="resize-container">
        <vue-croppie
          ref="croppie"
          :boundary="{ height: Math.max(resize[1]*1.5, 400), width: resize[0]*1.1 }"
          :viewport="{ height: resize[1], width: resize[0] }"
          :enable-resize="false"
        />
      </div>
    </b-modal>
  </div>
</template>

<script>
import VueCroppie from 'vue-croppie/src/VueCroppieComponent'
import bModal from '@b/components/modal/modal'
import { uploadFile } from '@/api/uploads'

export default {
  components: { bModal, VueCroppie },
  props: {
    value: {
      type: String,
      default: null
    },
    image: {
      type: Boolean,
      default: false
    },
    resize: {
      type: Array,
      default: null
    }
  },
  data () {
    return {
      isLoading: false,
      newFilename: null
    }
  },
  computed: {
    filename () {
      if (!this.value) return false
      let f = this.value.split('/')
      return f[f.length - 1]
    },
    accept () {
      if (this.image) return 'image/*'
      else return ''
    }
  },
  methods: {
    openUploadDialog () {
      if (this.isLoading) return
      this.$refs.uploadElement.click()
    },
    onFileChange () {
      const file = this.$refs.uploadElement.files[0]
      const filename = file.name
      const reader = new FileReader()
      this.isLoading = true
      if (this.image && this.resize) {
        reader.onload = (res) => {
          this.isLoading = false
          this.newFilename = filename
          this.openResizeDialog(res.target.result)
        }
        reader.readAsDataURL(file)
      } else {
        reader.onload = (res) => {
          this.uploadFile(filename, btoa(res.target.result))
        }
        reader.readAsBinaryString(file)
      }
    },
    async uploadFile (filename, data) {
      try {
        const res = await uploadFile(filename, data)
        this.$emit('change', res)
      } catch (err) {
        console.error(err)
      }
      this.isLoading = false
    },
    openResizeDialog (dataUrl) {
      this.$refs['upload-modal'].show()
      this.$refs.croppie.bind({
        url: dataUrl
      })
    },
    cropImage () {
      this.isLoading = true
      this.$refs.croppie.result({
        type: 'base64'
      }, (output) => {
        this.uploadFile(this.newFilename, output.split('base64,')[1])
      })
    }
  }
}
</script>
<style lang="scss">
@import "~croppie/croppie.css";
</style>

<style lang="scss" scoped>

.preview {
  height: 100px;
  width: 100px;
  background-color: #eee;
  padding: 0;
  img {
    height: 100px;
    width: 100px;
  }
}
.resize-container {
  padding-bottom: 3em;
  min-height: 200px;
}
</style>
