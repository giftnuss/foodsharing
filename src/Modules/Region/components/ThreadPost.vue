<template>
  <div class="bootstrap">
    <div :class="{card:true, disabledLoading: this.isLoading, 'mb-2':true}">
        <div class="card-header">
            {{ post.fs_name }}
        </div>
        <div class="card-body row">
            <div class="col-3 avatarSide text-center">
                <a :href="`/profile/${post.fs_id}`">
                    <Avatar 
                        :url="post.avatar.imageUrl"
                        :size="post.avatar.size"
                        :sleepStatus="post.avatar.user.sleep_status"
                    />
                </a>
                <a @click="openChat"  class="btn btn-sm btn-outline-primary"><i class="fa fa-comments" /> {{ 'chat.open_chat'|i18n }}</a>
            </div>
            <div class="col">
                {{ post.body }}
            </div>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-4 text-muted">
                    <small>{{ post.time }}</small>
                </div>
                <div class="col text-right">
                    <ThreadPostActions 
                        :givenEmojis="givenEmojis" 
                        :mayDeletePost="post.mayDeletePost"
                        @delete="deletePost"
                        @emojiAdd="emojiAdd"
                        @emojiRemove="emojiRemove"
                    />
                </div>
            </div>
        </div>
    </div>
  </div>
</template>

<script>
import Avatar from '@/components/Avatar'
import ThreadPostActions from './ThreadPostActions'
import conv from '@/conv'

export default {
  components: { Avatar, ThreadPostActions },
  props: {
    post: {}
  },
  data() {
      return {
          isLoading: false,
          givenEmojis: [
              {key: 'banana', count: 3, mine: false }, 
              {key: 'thumbsup', count: 1, mine: false } 
          ]
      }
  },
  computed: {

  },
  methods: {
    openChat() {
        conv.userChat(this.post.fs_id)
    },
    deletePost() {
        this.isLoading = true
        console.log('delete')
    },
    emojiAdd(key) {
        let emojiKeys = this.givenEmojis.map(e => e.key)
        let index = emojiKeys.indexOf(key)

        if(index !== -1) {
            // emoji alrready in list, increase count by 1
            if(this.givenEmojis[index].mine) return // already given - abort
            this.givenEmojis[index].count++
            this.givenEmojis[index].mine = true
        } else {
            // emoji not in the list yet, append it
            this.givenEmojis.push({ key, count: 1, mine: true })
        }

        // TODO: call api
    },
    emojiRemove(key) {
        let emojiKeys = this.givenEmojis.map(e => e.key)
        let index = emojiKeys.indexOf(key)

        if(!this.givenEmojis[index].mine) return 

        this.givenEmojis[index].count--
        this.givenEmojis[index].mine = false

        // TODO: call api
    }
  }
}
</script>

<style lang="scss" scoped>
    .avatarSide {
        border-right: 1px solid #eee;
    }
</style>
