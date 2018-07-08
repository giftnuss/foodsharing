<template>
  <div
    :class="{disabledLoading: isLoading}"
    class="bootstrap">
    <div v-if="isLoading">
      <div class="card-header text-white bg-primary">
        {{ title || '...' }}
      </div>
      <div class="card-body p-5"/>
    </div>

    <div
      v-if="!isLoading && id"
      class="card rounded">
      <div class="card-header text-white bg-primary">
        <div class="row">
          <div class="col text-truncate ml-2 pt-1 font-weight-bold">
            {{ title }}
          </div>
          <div class="col text-right">
            <a
              class="btn btn-sm btn-secondary"
              @click="toggleFollow">
              {{ $i18n(isFollowing ? 'forum.unfollow' : 'forum.follow') }}
            </a>
            <a
              v-if="mayModerate"
              class="btn btn-sm btn-secondary"
              @click="toggleStickyness">
              {{ $i18n(isSticky ? 'forum.unstick' : 'forum.stick') }}
            </a>
          </div>
        </div>
      </div>
    </div>
    <div
      v-for="post in posts"
      :key="post.id">
      <ThreadPost
        :author="post.author"
        :body="post.body"
        :reactions="post.reactions"
        :may-delete="true"
        :may-edit="true"
        :is-loading="loadingPosts.indexOf(post.id) != -1"
        :createdAt="new Date(post.createdAt)"
        @delete="deletePost(post)"
        @toggleFollow="toggleFollow"
        @reactionAdd="reactionAdd(post, arguments[0])"
        @reactionRemove="reactionRemove(post, arguments[0])"
      />
    </div>
    <div
      v-if="!isLoading && !errorMessage && !posts.length"
      class="alert alert-warning"
      role="alert">
      Bisher keine Beiträge vorhanden
    </div>
    <div
      v-if="errorMessage"
      class="alert alert-danger"
      role="alert">
      <strong>{{ $i18n('error_unexpected') }}:</strong> {{ errorMessage }}
    </div>
    <ThreadForm
      ref="form"
      :is-following="isFollowing"
      :error-message="errorMessage"
      @submit="createPost"
      @toggleFollow="toggleFollow" />
  </div>
</template>

<script>
import ThreadPost from './ThreadPost'
import ThreadForm from './ThreadForm'
import * as api from '@/api/forum'
import { pulseError } from '@/script'
import i18n from '@/i18n'
import { user } from '@/server-data'

export default {
  components: { ThreadPost, ThreadForm },
  props: {
    id: {
      type: Number,
      default: null
    }
  },
  data () {
    return {
      title: '',
      posts: [],

      isSticky: true,
      isActive: true,
      mayModerate: false,
      isFollowing: true,

      isLoading: false,
      loadingPosts: [],
      errorMessage: null
    }
  },
  async created () {
    this.isLoading = true
    this.reload()
  },
  methods: {
    async reload () {
      try {
        let res = (await api.getThread(this.id)).data
        Object.assign(this, {
          title: res.title,
          posts: res.posts,
          isSticky: res.isSticky,
          isActive: res.isActive,
          mayModerate: res.mayModerate,
          isFollowing: res.isFollowing
        })
        this.isLoading = false
      } catch (err) {
        this.isLoading = false
        this.errorMessage = err.message
      }
    },

    async deletePost (post) {
      this.loadingPosts.push(post.id)

      try {
        await api.deletePost(post.id)
        await this.reload()
      } catch (err) {
        pulseError(i18n('error_unexpected'))
      } finally {
        this.loadingPosts.splice(this.loadingPosts.indexOf(post.id), 1)
      }
    },

    async reactionAdd (post, key, onlyLocally = false) {
      if (post.reactions[key]) {
        // reaction alrready in list, increase count by 1
        if (post.reactions[key].find(r => r.id === user.id)) return // already given - abort
        post.reactions[key].push({ id: user.id, name: user.firstname })
      } else {
        // reaction not in the list yet, append it
        this.$set(post.reactions, key, [{ id: user.id, name: user.firstname }])
      }

      if (!onlyLocally) {
        try {
          await api.addReaction(post.id, key)
        } catch (err) {
          // failed? remove it again
          this.reactionRemove(post, key, true)
          pulseError(i18n('error_unexpected'))
        }
      }
    },
    async reactionRemove (post, key, onlyLocally = false) {
      let reactionUser = post.reactions[key].find(r => r.id === user.id)

      if (!reactionUser) return

      post.reactions[key].splice(post.reactions[key].indexOf(reactionUser), 1)

      if (!onlyLocally) {
        try {
          await api.removeReaction(post.id, key)
        } catch (err) {
          // failed? add it again
          this.reactionAdd(post, key, true)
          pulseError(i18n('error_unexpected'))
        }
      }
    },
    async toggleFollow () {
      let targetState = !this.isFollowing
      this.isFollowing = targetState
      try {
        if (targetState) {
          await api.followThread(this.id)
        } else {
          await api.unfollowThread(this.id)
        }
      } catch (err) {
        // failed? undo it
        this.isFollowing = !targetState
        pulseError(i18n('error_unexpected'))
      }
    },
    async toggleStickyness () {
      let targetState = !this.isSticky
      this.isSticky = targetState
      try {
        if (targetState) {
          await api.stickThread(this.id)
        } else {
          await api.stickThread(this.id)
        }
      } catch (err) {
        // failed? undo it
        this.isSticky = !targetState
        pulseError(i18n('error_unexpected'))
      }
    },
    async createPost (body) {
      this.errorMessage = null
      let dummyPost = {
        id: -1,
        time: new Date(),
        body: body,
        reactions: {},
        author: {
          name: `${user.firstname} ${user.lastname}`,
          avatar: {
            size: 130,
            url: user.avatar['130']
          }
        }
      }
      this.loadingPosts.push(-1)
      this.posts.push(dummyPost)

      try {
        await api.createPost(this.id, body)
        await this.reload()
      } catch (err) {
        let index = this.posts.indexOf(dummyPost)
        this.posts.splice(index, 1)

        this.errorMessage = err.message
        this.$refs.form.text = body
      }
    }
  }
}
</script>

<style lang="scss" scoped>

</style>
