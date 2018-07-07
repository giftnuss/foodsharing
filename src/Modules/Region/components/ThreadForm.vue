<template>
  <div class="bootstrap">
      <div class="card">
            <div class="card-header">
              Antworten
            </div>
            <div class="card-body">
                <div v-if="errorMessage" class="alert alert-danger" role="alert">
                    <strong>{{ $i18n('forum.sending_error') }}:</strong> {{ errorMessage }}
                </div>
                <textarea class="form-control" v-model="text" rows="3"  @keyup.shift.enter="submit"></textarea>
            </div>
            <div class="card-footer">
              <div class="row">
                   <div class="col ml-2 pt-2">
                        <b-form-checkbox v-model="subscribe" @keyup.shift.enter="submit">
                            {{ $i18n('forum.subscribe_thread') }}
                        </b-form-checkbox>
                        
                   </div>
                   <div class="col-2 text-right">
                       <button class="btn btn-secondary" @click="submit" :disabled="!text.trim()">Senden</button>
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
  data() {
      return {
          text: '',
          subscribe: false,
      }
  },
  props: {
      errorMessage: {}
  },
  methods: {
    submit() {
        if(!this.text.trim()) return
        this.$emit('submit', this.text.trim(), this.subscribe)
        this.text = ''
    },
  }
}
</script>

<style lang="scss" scoped>

</style>
