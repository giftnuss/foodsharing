<template>
  <div class="container bootstrap">
    <div v-if="mayPost">
      <div class="form-group">
        <textarea
          id="wallpost-body"
          v-model="text"
          :placeholder="i18n('wall.message_placeholder')"
          rows="3"
          class="form-control"
        />
      </div>
      <div
        id="wallpost-submit"
        class="text-right"
      >
        <button
          class="btn btn-secondary btn-sm"
          @click="addPost"
        >
          {{ i18n('button.send') }}
        </button>
      </div>
      <span v-if="isSending">
        Sending...
      </span>
      <span v-if="error">
        Es ist ein Fehler aufgetreten
      </span>
    </div>
    <div class="wall-posts">
      <table class="pintable">
        <tbody>
          <tr
            v-for="post in posts"
            :key="post.id"
            :class="['bpost', `wallpost-${post.id}`]"
          >
            <td class="img">
              <a :href="`/profile/${post.author.id}`">
                <img :src="post.author.avatar">
              </a>
            </td>
            <td>
              <span class="msg">
                {{ post.body }}
              </span>
              <span v-if="post.gallery">
                <img
                  v-for="img in post.gallery"
                  :key="img.thumb"
                  :src="img.thumb"
                >
              </span>
              <div class="foot">
                <span class="time">
                  {{ post.createdAt }} Uhr von {{ post.author.name }}
                </span>
                <button
                  v-if="mayDelete"
                  @click="deletePost(post)"
                >
                  Delete
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import i18n from '@/i18n'
import { getWallPosts, addPost, deletePost } from '@/api/wall'

export default {
  props: {
    target: {
      type: String,
      required: true
    },
    targetId: {
      type: Number,
      required: true
    }
  },
  data () {
    return {
      posts: [],
      text: '',
      isSending: false,
      error: false,
      mayDelete: false,
      mayPost: false
    }
  },
  async created () {
    const data = await getWallPosts(this.target, this.targetId)
    this.posts = data.results
    this.mayDelete = data.mayDelete
    this.mayPost = data.mayPost
  },
  methods: {
    async addPost () {
      try {
        this.isSending = true
        const result = await addPost(this.target, this.targetId, this.text)
        this.posts.unshift(result.post)
      } catch (err) {
        this.error = true
      } finally {
        this.isSending = false
      }
    },
    async deletePost (post) {
      try {
        this.isSending = true
        await deletePost(this.target, this.targetId, post.id)
      } finally {
        this.isSending = false
        let id = this.posts.indexOf(post)
        if (id !== -1) {
          this.posts.splice(id, 1)
        }
      }
    },
    i18n (key) {
      return i18n(key)
    }
  }

}
</script>
