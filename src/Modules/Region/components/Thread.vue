<template>
  <div class="bootstrap">
    <div class="card rounded">
        <div class="card-header text-white bg-primary">
            <div class="row">
                <div class="col text-truncate ml-2 pt-1 font-weight-bold">
                    {{ title }}
                </div>
                <div class="col text-right">
                        <a class="btn btn-sm btn-secondary" @click="toggleFollow">
                            {{ $i18n(isFollowing ? 'forum.unfollow' : 'forum.follow') }}
                        </a>
                        <a class="btn btn-sm btn-secondary" @click="toggleStickyness">
                            {{ $i18n(isSticked ? 'forum.unstick' : 'forum.stick') }}
                        </a>
                </div>
            </div>
        </div>
    </div>
    <div v-for="post in posts" :key="post.id">
        <ThreadPost 
            :author="post.author"
            :body="post.body"
            :reactions="post.reactions"
            :mayDelete="true"
            :mayEdit="true"
            :isLoading="loadingPosts.indexOf(post.id) != -1"
            :time="new Date(post.time)"
            @delete="deletePost(post)"
            @toggleFollow="toggleFollow"
            @reactionAdd="reactionAdd(post, arguments[0])"
            @reactionRemove="reactionRemove(post, arguments[0])"
        />
    </div>
    <ThreadForm 
        @submit="createPost"
        @toggleFollow="toggleFollow"
        :isFollowing="isFollowing"
        :errorMessage="errorMessage" ref="form" />
  </div>
</template>

<script>
import ThreadPost from './ThreadPost'
import ThreadForm from './ThreadForm'
import * as api from '@/api/forum'
import { pulseError } from '@/script'
import i18n from '@/i18n'

export default {
  components: { ThreadPost, ThreadForm },
  data() {
      return {
          id: null,
          regionId: null,
          title: '',
          posts: [],
          loadingPosts: [],
          isFollowing: true,
          isSticked: true,
          mayChangeStickyness: true,
          errorMessage: null
      }
  },
  created() {
      // TODO: get data by API instead of HTML created by FormControl.php
  },
  methods: {
    async deletePost(post) {
        this.loadingPosts.push(post.id)

        try {
            await api.deletePost(post.id)
            let index = this.posts.indexOf(post)
            this.posts.splice(index, 1)
        } catch(err) {
            pulseError(i18n('error_unexpected'))
            this.loadingPosts.splice(this.loadingPosts.indexOf(post.id), 1)
        }
    },
    getReactionFromPost(post, key) {
        let reactionKeys = post.reactions.map(e => e.key)
        let index = reactionKeys.indexOf(key)

        if(index === -1)  return null

        return post.reactions[index]
    },
    async reactionAdd(post, key) {
        let reaction = this.getReactionFromPost(post, key)

        if(reaction) {
            // reaction alrready in list, increase count by 1
            if(reaction.mine) return // already given - abort
            reaction.count++
            reaction.mine = true
        } else {
            // reaction not in the list yet, append it
            post.reactions.push({ key, count: 1, mine: true })
        }
    
        try {
            await api.addReaction(post.id, key)
        } catch(err) {
            // failed? remove it again
            let reaction = this.getReactionFromPost(post, key)
            reaction.count--
            reaction.mine = false
            pulseError(i18n('error_unexpected'))
        }
    },
    async reactionRemove(post, key) {
        let reaction = this.getReactionFromPost(post, key)

        if(!reaction.mine) return 

        reaction.count--
        reaction.mine = false

        try {
            await api.removeReaction(post.id, key)
        } catch(err) {
            // failed? add it again
            let reaction = this.getReactionFromPost(post, key)
            reaction.count++
            reaction.mine = true
            pulseError(i18n('error_unexpected'))
        }
    },
    async toggleFollow() {
        let targetState = !this.isFollowing
        this.isFollowing = targetState
        try {
            if(targetState) {
                await api.followThread(this.id)
            } else {
                await api.unfollowThread(this.id)
            }
        } catch(err) {
            // failed? undo it
            this.isFollowing = !targetState
            pulseError(i18n('error_unexpected'))
        }
    },
    async toggleStickyness() {
        let targetState = !this.isSticked
        this.isSticked = targetState
        try {
            if(targetState) {
                await api.stickThread(this.id)
            } else {
                await api.stickThread(this.id)
            }
        } catch(err) {
            // failed? undo it
            this.isSticked = !targetState
            pulseError(i18n('error_unexpected'))
        }
    },
    async createPost(body) {
        this.errorMessage = null

        let dummyPost = {
            id: -1,
            time: new Date,
            body: body,
            reactions: [],
            author: {
                // TODO: implement some global user state
                name: 'dummyName',
                avatar: {
                    size: 130,
                    url: '/img/130_q_avatar.png' 
                }
            }
        }
        this.loadingPosts.push(-1)
        this.posts.push(dummyPost)

        try {
            let post = await api.createPost(this.id, body)

            // replace dummy post with the one from the api
            this.posts[this.posts.indexOf(dummyPost)] = post

        } catch(err) {
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
