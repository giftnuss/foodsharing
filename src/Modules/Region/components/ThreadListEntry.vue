<!-- eslint-disable vue/max-attributes-per-line -->
<template>
  <li :id="`thread-${thread.id}`" class="thread">
    <a class="ui-corner-all" :href="threadUrl">
      <span class="user_pic">
        <Avatar
          :url="thread.lastPost.author.avatar"
          :sleep-status="thread.lastPost.author.sleepStatus"
          :size="50"
        />
      </span>
      <span class="thread_title" :class="{'font-weight-bold': thread.isSticky}">
        {{ thread.title }}
      </span>
      <span class="last_post ui-corner-all">
        <span class="time">{{ lastPostDate }}</span>
        <span class="info">
          {{ $i18n('forum.from', { name: thread.lastPost.author.name || '' }) }}
        </span>
      </span>
      <!-- TODO ... -->
      <span style="clear: both;" />
    </a>
  </li>
</template>

<script>
import Avatar from '@/components/Avatar'

import dateFnsParseISO from 'date-fns/parseISO'
import { url } from '@/urls'

export default {
  components: { Avatar },
  props: {
    thread: { type: Object, required: true }
  },
  computed: {
    threadUrl () {
      return url('forum', this.thread.regionId, this.thread.regionSubId, this.thread.id)
    },
    lastPostDate () {
      return this.$dateDistanceInWords(dateFnsParseISO(this.thread.lastPost.createdAt))
      // TODO the relative display is nicer, but it should show the exact date on hover as well
    }
  }
}
</script>

<style lang="scss" scoped>
.thread {
  .user_pic,
  .thread_title {
    float: left;
    display: block;
  }

  .user_pic {
    margin-right: 10px;
    width: 64px;
    height: 50px;
    background-image: url(/img/forum_bubble.png);
  }

  .user_pic .avatar ::v-deep img {
    border-radius: 5px;
  }

  .thread_title {
    font-size: 15px;
    margin-top: 15px !important;
    margin-left: 5px !important;
    color: var(--fs-brown);
    width: 55%;
  }

  .last_post {
    float: right;
    padding: 7px;
    width: 170px;
  }

  &:hover {
    .user_pic,
    .thread_title {
      color: var(--white);
    }

    .last_post {
      &, & > span {
        color: var(--fs-brown);
        background-color: var(--white);
      }
    }
  }
}
</style>
