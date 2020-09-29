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

    <ul class="posts list-unstyled">
      <WallPost
        v-for="p in posts"
        :key="p.id"
        :post="p"
        :managers="managers"
        :may-delete-everything="mayDeleteEverything"
        class="wallpost"
        @deletePost="deletePost"
      />
    </ul>
  </div>
</template>

<script>
import { getStoreWall, deleteStorePost, writeStorePost } from '@/api/stores'
import WallPost from '../../WallPost/components/WallPost'
import { showLoader, hideLoader, pulseError } from '@/script'
import i18n from '@/i18n'

export default {
  components: { WallPost },
  props: {
    storeId: { type: Number, required: true },
    managers: { type: Array, default: () => [] },
    mayWritePost: { type: Boolean, required: true },
    mayDeleteEverything: { type: Boolean, required: true }
  },
  data () {
    return {
      loaded: {},
      posts: [],
      newPostText: ''
    }
  },
  computed: {
    newPostExists () {
      return this.newPostText.trim().length > 0
    }
  },
  async created () {
    this.loadPosts()
  },
  methods: {
    async loadPosts () {
      if (this.loaded.posts) return
      if (!this.posts.length) {
        this.posts = (await getStoreWall(this.storeId))
      }
      this.loaded.posts = true
    },
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
    },
    async deletePost (postId) {
      try {
        showLoader()
        await deleteStorePost(this.storeId, postId)
        const index = this.posts.findIndex(post => post.id === postId)
        if (index >= 0) {
          this.posts.splice(index, 1)
        }
      } catch (e) {
        if (e.code === 403) {
          pulseError(i18n('wall.error-delete'))
        } else {
          pulseError(i18n('error_unexpected'))
          console.error(e.code)
        }
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

ul.posts {
  margin: 0;
}
</style>
