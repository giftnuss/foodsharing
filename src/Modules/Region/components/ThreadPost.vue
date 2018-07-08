<template>
  <div class="bootstrap">
    <div :class="{card:true, disabledLoading: isLoading, 'mb-2':true}">
        <div class="card-header">
            {{ author.name }}
        </div>
        <div class="card-body row">
            <div class="col-3 avatarSide text-center">
                <a :href="$url('profile', author.id)">
                    <Avatar 
                        :url="author.avatar.url"
                        :size="author.avatar.size"
                        :sleepStatus="author.sleepStatus"
                    />
                </a>
                <a @click="openChat"  class="btn btn-sm btn-outline-primary"><i class="fa fa-comments" /> {{ $i18n('chat.open_chat') }}</a>
            </div>
            <div class="col">
                {{ body }}
            </div>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-4 text-muted">
                    <small>{{ time | dateFormat('dddd, Do MMM YYYY, HH:mm [Uhr]') }}</small>
                </div>
                <div class="col text-right">
                    <ThreadPostActions 
                        :reactions="reactions" 
                        :mayDelete="mayDelete"
                        :mayEdit="mayEdit"
                        @delete="$emit('delete')"
                        @reactionAdd="$emit('reactionAdd', $event)"
                        @reactionRemove="$emit('reactionRemove', $event)"
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
    body: String,
    author: Object,
    time: Date,
    reactions: Array,
    mayEdit: Boolean,
    mayDelete: Boolean,
    isLoading: Boolean,
  },
  methods: {
    openChat() {
        conv.userChat(this.author.id)
    }
  }
}
</script>

<style lang="scss" scoped>
    .avatarSide {
        border-right: 1px solid #eee;
    }
</style>
