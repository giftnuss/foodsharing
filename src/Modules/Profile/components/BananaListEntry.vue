<template>
  <div class="banana-container d-flex my-1 py-2">
    <a
      v-b-tooltip.hover="$i18n('profile.go')"
      :href="$url('profile', authorId)"
    >
      <Avatar
        :url="avatar"
        :size="50"
        class="member-pic mt-1 pr-2 pt-1"
        :auto-scale="false"
      />
    </a>
    <div>
      <div class="time p-1">
        <a :href="$url('profile', authorId)">
          {{ authorName }}
        </a>
        <i class="fas fa-fw fa-angle-right" />
        {{ $dateFormat(when, 'full-long') }}
      </div>
      <!-- For whitespace and layout reasons, the text needs to be enclosed directly: -->
      <!-- eslint-disable-next-line vue/singleline-html-element-content-newline -->
      <div class="msg ml-1 p-1 pl-2">{{ text }}</div>
    </div>
  </div>
</template>

<script>
import dateFnsParseISO from 'date-fns/parseISO'

import Avatar from '@/components/Avatar'

export default {
  components: { Avatar },
  props: {
    authorId: { type: Number, required: true },
    authorName: { type: String, default: '' },
    avatar: { type: String, default: '' },
    createdAt: { type: String, required: true },
    text: { type: String, default: '' },
  },
  data () {
    return {
      when: dateFnsParseISO(this.createdAt),
    }
  },
}
</script>

<style lang="scss" scoped>
.banana-container {
  border-top: 1px solid var(--border);

  .member-pic ::v-deep img {
    width: 50px;
    height: 50px;
  }

  .msg {
    white-space: pre-line;
    border-left: 3px solid var(--border);
  }

  .time a {
    color: var(--secondary);
    font-weight: bolder;
  }
}
</style>
