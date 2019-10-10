<template>
  <div class="bootstrap">
    <div class="card">
      <div class="card-header">
        Antworten
      </div>
      <div class="card-body">
        <p v-html="$i18n('forum.markdown_description')" />
        <textarea
          ref="textarea"
          v-model="text"
          @keyup.ctrl.enter="submit"
          class="form-control"
          rows="3"
        />
      </div>
      <div class="card-footer">
        <div class="row">
          <div class="col ml-2 pt-2">
            <b-form-checkbox
              :checked="isFollowing"
              @change="$emit('toggleFollow')"
            >
              {{ $i18n('forum.subscribe_thread') }}
            </b-form-checkbox>
          </div>
          <div class="col-auto text-right">
            <button
              :disabled="!text.trim()"
              @click="submit"
              class="btn btn-secondary"
            >
              Senden
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { BFormCheckbox } from 'bootstrap-vue'

export default {
  components: { BFormCheckbox },
  props: {
    isFollowing: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      text: ''
    }
  },
  methods: {
    submit () {
      if (!this.text.trim()) return
      this.$emit('submit', this.text.trim())
      this.text = ''
    },
    focus () {
      this.$refs.textarea.focus()
    }
  }
}
</script>

<style lang="scss" scoped>

</style>
