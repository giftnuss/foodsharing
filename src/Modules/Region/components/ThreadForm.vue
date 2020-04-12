<template>
  <div class="bootstrap">
    <div class="card">
      <div class="card-header">
        {{ $i18n('button.answer') }}
      </div>
      <div class="card-body">
        <p v-html="$i18n('forum.markdown_description')" />
        <textarea
          ref="textarea"
          v-model="text"
          class="form-control"
          rows="3"
          @keyup.ctrl.enter="submit"
        />
      </div>
      <div class="card-footer below">
        <div class="row">
          <div class="col">
            <button
              :disabled="!text.trim()"
              class="btn btn-primary float-right"
              @click="submit"
            >
              {{ $i18n('button.send') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>

export default {
  props: {},
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
