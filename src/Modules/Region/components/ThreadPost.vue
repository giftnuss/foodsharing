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
              :sleep-status="author.sleepStatus"
            />
          </a>
          <a
            class="btn btn-sm btn-outline-primary"
            @click="openChat"><i class="fa fa-comments" /> {{ $i18n('chat.open_chat') }}</a>
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
              :may-delete="mayDelete"
              :may-edit="mayEdit"
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
    body: { type: String, default: '' },
    author: { type: Object, default: () => ({ avatar: {} }) },
    time: { type: Date, default: null },
    reactions: { type: Array, default: () => [] },
    mayEdit: { type: Boolean, default: false },
    mayDelete: { type: Boolean, default: false },
    isLoading: { type: Boolean, default: true }
  },
  methods: {
    openChat () {
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
