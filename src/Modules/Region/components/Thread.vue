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
            :isLoading="loadingPosts.indexOf(post.id) != -1"
            :time="new Date(post.time)"
            @delete="deletePost(post)"
            @reactionAdd="reactionAdd(post, arguments[0])"
            @reactionRemove="reactionRemove(post, arguments[0])"
        />
    </div>
    <ThreadForm @submit="createPost" :errorMessage="errorMessage" ref="form" />
  </div>
</template>

<script>
import ThreadPost from './ThreadPost'
import ThreadForm from './ThreadForm'

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
    deletePost(post) {
        this.loadingPosts.push(post.id)

        // TODO: call api

        setTimeout( () => {
            let index = this.posts.indexOf(post)
            this.posts.splice(index, 1)
        }, 1000)
    },
    reactionAdd(post, key) {
        let reactionKeys = post.reactions.map(e => e.key)
        let index = reactionKeys.indexOf(key)

        if(index !== -1) {
            // reaction alrready in list, increase count by 1
            if(post.reactions[index].mine) return // already given - abort
            post.reactions[index].count++
            post.reactions[index].mine = true
        } else {
            // reaction not in the list yet, append it
            post.reactions.push({ key, count: 1, mine: true })
        }
    
        // TODO: call api
    },
    reactionRemove(post, key) {
        let reactionKeys = post.reactions.map(e => e.key)
        let index = reactionKeys.indexOf(key)

        if(!post.reactions[index].mine) return 

        post.reactions[index].count--
        post.reactions[index].mine = false

        // TODO: call api
    },
    toggleFollow() {
        this.isFollowing = !this.isFollowing
        // TODO: call api
    },
    toggleStickyness() {
        this.isSticked = !this.isSticked
        // TODO: call api
    },
    createPost(body, subscribe) {
        this.errorMessage = null
        if((subscribe && !this.isFollowing) || !subscribe && this.isFollowing) {
            this.toggleFollow()
        }

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

        setTimeout( () => {
            try {
                // TODO: handle API call
                // replace dummyPost with response post
                if(Math.random() > 0.5) {
                    dummyPost.id = Math.random()*1000
                } else {
                    throw new Error('Timeout')
                }
            } catch(err) {
                let index = this.posts.indexOf(dummyPost)
                this.posts.splice(index, 1)

                this.errorMessage = err.message
                this.$refs.form.text = body
            }
        }, 1000)
        
    }
  }
}
</script>

<style lang="scss" scoped>

</style>
