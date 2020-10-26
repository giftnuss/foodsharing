<template>
  <!-- eslint-disable-next-line vue/max-attributes-per-line -->
  <div :id="`post-${id}`" class="bootstrap">
    <!-- eslint-disable-next-line vue/max-attributes-per-line -->
    <div class="card mb-2" :class="{'disabledLoading': isLoading}">
      <div class="card-header d-flex">
        <span class="author">
          {{ author.name }}
        </span>
        <ThreadPostDate
          v-if="wXS"
          :link="deepLink"
          :date="createdAt"
          classes="ml-auto"
          @scroll="$emit('scroll', $event)"
        />
      </div>
      <div class="card-body row">
        <div class="col-sm-3 avatarSide text-center">
          <a :href="$url('profile', author.id)">
            <Avatar
              :url="author.avatar"
              :sleep-status="author.sleepStatus"
              :size="130"
              class="mb-2"
              :auto-scale="false"
            />
          </a>
          <a
            v-if="!wXS && !isMe"
            class="btn btn-sm btn-outline-primary"
            @click="openChat"
          >
            <i class="fas fa-fw fa-comments" /> {{ $i18n('chat.open_chat') }}
          </a>
        </div>
        <div
          class="col-sm-9"
          v-html="body"
        />
      </div>
      <div class="card-footer">
        <div class="row">
          <ThreadPostDate
            v-if="!wXS"
            :link="deepLink"
            :date="createdAt"
            classes="col-auto text-muted pt-1 pl-3"
            @scroll="$emit('scroll', $event)"
          />
          <div class="col text-right">
            <ThreadPostActions
              :reactions="reactions"
              :may-delete="mayDelete"
              :may-edit="mayEdit"
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
import ThreadPostDate from './ThreadPostDate'
import conv from '@/conv'
import MediaQueryMixin from '@/utils/VueMediaQueryMixin'

export default {
  components: { Avatar, ThreadPostActions, ThreadPostDate },
  mixins: [MediaQueryMixin],
  props: {
    id: { type: Number, default: null },
    userId: { type: Number, required: true },
    body: { type: String, default: '' },
    author: { type: Object, default: () => ({ avatar: null }) },
    createdAt: { type: Date, default: null },
    deepLink: { type: String, default: '' },
    reactions: { type: Object, default: () => ({}) },
    mayEdit: { type: Boolean, default: false },
    mayDelete: { type: Boolean, default: false },
    isLoading: { type: Boolean, default: true },
  },
  computed: {
    isMe () {
      return this.userId === this.author.id
    },
  },
  methods: {
    openChat () {
      conv.userChat(this.author.id)
    },
  },
}
</script>

<style lang="scss" scoped>
    .avatarSide {
        border-right: 1px solid #eee;
    }
</style>
