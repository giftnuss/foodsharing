<!-- eslint-disable vue/max-attributes-per-line -->
<template>
  <div class="store-wall">
    <div
      v-if="mayWritePost"
      class="newpost m-1 p-1"
    >
      <b-form-textarea
        id="newpost"
        v-model="newPostText"
        :placeholder="$i18n('wall.message_placeholder')"
        rows="2"
        max-rows="6"
      />

      <div class="submit d-flex">
        <b-button
          class="ml-auto mt-2"
          :class="{'d-none': !newPostExists}"
          variant="outline-secondary"
          :disabled="!newPostExists"
          squared
          @click.prevent.stop="writePost"
        >
          {{ $i18n('button.send') }}
        </b-button>
      </div>
    </div>
  </div>
</template>

<script>
import { writeStorePost } from '@/api/stores'
import { showLoader, hideLoader, pulseError } from '@/script'
import i18n from '@/i18n'

export default {
  props: {
    storeId: { type: Number, required: true },
    mayWritePost: { type: Boolean, required: true }
  },
  data () {
    return {
      newPostText: ''
    }
  },
  computed: {
    newPostExists () {
      return this.newPostText.trim().length > 0
    }
  },
  methods: {
    async writePost () {
      const text = this.newPostText.trim()
      if (!text) return
      try {
        showLoader()
        this.newPostText = ''
        const newPost = (await writeStorePost(this.storeId, text))
        this.posts.unshift(newPost)
      } catch (e) {
        console.error(e)
        pulseError(i18n('wall.error-create'))
        this.newPostText = text
      } finally {
        hideLoader()
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.newpost textarea#newpost {
  overflow-y: auto !important;
}
</style>
