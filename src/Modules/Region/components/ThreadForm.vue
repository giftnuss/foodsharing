<template>
  <div class="bootstrap">
    <div class="card">
      <div class="card-header">
        Antworten
      </div>
      <div class="card-body">
        <textarea
          v-model="text"
          class="form-control"
          rows="3"
          @keyup.shift.enter="submit"/>
      </div>
      <div class="card-footer">
        <div class="row">
          <div class="col ml-2 pt-2">
            <b-form-checkbox
              :checked="isFollowing"
              @change="$emit('toggleFollow')" >
              {{ $i18n('forum.subscribe_thread') }}
            </b-form-checkbox>

          </div>
          <div class="col-2 text-right">
            <button
              :disabled="!text.trim()"
              class="btn btn-secondary"
              @click="submit">Senden</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import bFormCheckbox from '@b/components/form-checkbox/form-checkbox'

export default {
  components: { bFormCheckbox },
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
    }
  }
}
</script>

<style lang="scss" scoped>

</style>
