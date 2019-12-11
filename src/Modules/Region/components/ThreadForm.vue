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
          class="form-control"
          rows="3"
          @keyup.ctrl.enter="submit"
        />
      </div>
      <div class="card-footer">
        <div class="row">
          <div class="col-auto">
            <button
              class="btn btn-secondary"
              @click="$emit('toggleFollowBell')"
            >
              {{ $i18n(isFollowingBell ? 'forum.unfollow.bell' : 'forum.follow.bell') }}
            </button>
            <button
              class="btn btn-secondary"
              @click="$emit('toggleFollowEmail')"
            >
              {{ $i18n(isFollowingEmail ? 'forum.unfollow.email' : 'forum.follow.email') }}
            </button>
            <button
              :disabled="!text.trim()"
              class="btn btn-secondary"
              @click="submit"
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

export default {
  props: {
    isFollowingEmail: {
      type: Boolean,
      default: false
    },
    isFollowingBell: {
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
