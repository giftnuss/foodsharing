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
              :url="author.avatar"
              :sleep-status="author.sleepStatus"
              size="130"
            />
          </a>
          <a
            v-if="!wXS"
            class="btn btn-sm btn-outline-primary"
            @click="openChat"><i class="fa fa-comments" /> {{ $i18n('chat.open_chat') }}</a>
        </div>
        <div
          class="col"
          v-html="body" />
      </div>
      <div class="card-footer">
        <div class="row">
          <div class="col-4 text-muted pt-1 pl-3">
            <small v-if="wXS">{{ createdAt | dateFormat('full-short') }}</small>
            <small v-else>{{ createdAt | dateFormat('full-long') }}</small>
          </div>
          <div class="col text-right">
            <ThreadPostActions
              :reactions="reactions"
              :may-delete="mayDelete"
              :may-edit="mayEdit"
              :is-mobile="wXS"
              @delete="$emit('delete')"
              @reactionAdd="$emit('reactionAdd', $event)"
              @reactionRemove="$emit('reactionRemove', $event)"
              @reply="$emit('reply', body)"
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
import MediaQueryMixin from '@/utils/VueMediaQueryMixin'

export default {
  components: { Avatar, ThreadPostActions },
  mixins: [MediaQueryMixin],
  props: {
    id: { type: Number, default: null },
    body: { type: String, default: '' },
    author: { type: Object, default: () => ({ avatar: {} }) },
    createdAt: { type: Date, default: null },
    reactions: { type: Object, default: () => ({}) },
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
