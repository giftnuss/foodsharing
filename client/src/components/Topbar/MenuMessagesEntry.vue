<template>
  <a
    :class="classes"
    href="#"
    @click="openChat"
  >
    <div class="row">
      <div class="col-2">
        <div :class="['avatar', `avatar_${avatars.length}`]">
          <div
            v-for="avatar in avatars"
            :key="avatar"
            :style="{backgroundImage: `url('${avatar || '/img/130_q_avatar.png'}')`}"
          />
        </div>
      </div>
      <div class="col-10">
        <div class="mt-1 d-flex w-100 justify-content-between">
          <h5 class="mb-1 text-truncate">
            {{ title }}
          </h5>
          <small class="text-muted text-right nowrap">
            {{ conversation.lastMessageTime | dateDistanceInWords }}
          </small>
        </div>
        <p class="mb-1 text-truncate">
          {{ conversation.lastMessage.bodyRaw }}
        </p>
      </div>
    </div>
  </a>
</template>
<script>
import serverData from '@/server-data'
import conv from '@/conv'
import { AVATAR_DEFAULT, GROUP_PICTURE_DEFAULT } from '@/consts'

export default {
  props: {
    conversation: {
      type: Object,
      default: () => ({})
    }
  },
  computed: {
    classes () {
      return [
        'list-group-item',
        'list-group-item-action',
        'flex-column',
        'align-items-start',
        this.conversation.hasUnreadMessages ? 'list-group-item-warning' : null
      ]
    },
    title () {
      if (this.conversation.title) return this.conversation.title
      let members = this.conversation.members
      // without ourselve
        .filter(m => m.id !== this.loggedinUser.id)

      return members.map(m => m.name).join(', ')
    },
    avatars () {
      let lastId = this.conversation.lastMessage.authorId
      let members = this.conversation.members

      // without ourselve
        .filter(m => m.id !== this.loggedinUser.id)

      // bring last participant to the top
        .sort((a, b) => {
          /* eslint-disable eqeqeq */
          if (a.id == lastId) return -1
          if (b.id == lastId) return 1
          return 0
        })

      // enough avatars for displaying?
      if (members.filter(m => m.avatar).length > 4) {
        members = members.filter(m => m.avatar)
      }

      // we dont need more then 4
      members = members.slice(0, 4)

      if (members.length) {
        return members.map(m => m.avatar)
      } else {
        if (this.conversation.members.length !== 2) {
          // default group picture
          return [GROUP_PICTURE_DEFAULT]
        } else {
          // default user picture
          return [AVATAR_DEFAULT]
        }
      }
    },
    loggedinUser () {
      return serverData.user
    }
  },
  methods: {
    openChat () {
      conv.chat(this.conversation.id)
      this.$emit('chatOpened')
    }
  }
}
</script>

<style lang="scss" scoped>
h5 {
    font-weight: bold;
    font-size: 0.9em;
}
p {
    font-size: 0.8em;
}
.list-group-item {
    padding: 0.4em 1em;
}

.avatar {
    height: 3em;
    width: 3em;
    line-height: 0.7em;
    margin-left: -0.5em;
    div {
        background-size: cover;
        background-position: center;
        display: inline-block;
    }
}
.avatar_1 div {
    height: 3em;
    width: 3em;
}
.avatar_2 div {
    height: 3em;
    width: 1.5em;
}
.avatar_3 div, .avatar_4 div {
    height: 1.5em;
    width: 1.5em;
}
.nowrap {
    white-space: nowrap;
}
</style>
