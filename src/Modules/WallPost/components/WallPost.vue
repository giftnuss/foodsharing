<!-- eslint-disable vue/max-attributes-per-line -->
<template>
  <li
    class="post d-flex flex-column justify-content-between align-items-center"
    :class="[`wallpost-${post.id}`]"
  >
    <div class="metadata text-muted w-100 mx-1 d-inline-flex">
      <span class="author flex-grow-1 flex-shrink-1 mr-1">
        <a :href="$url('profile', post.author.id)">
          {{ post.author.name }}
        </a>
      </span>

      <span
        v-b-tooltip="$dateFormat(post.createdAt, 'full-long')"
        class="datetime text-right flex-grow-0 flex-shrink-1"
      >
        <i class="far fa-fw fa-clock" />
        {{ $dateDistanceInWords(post.createdAt) }}
      </span>
    </div>

    <div class="content w-100 m-1 flex-grow-0 flex-shrink-0 d-flex">
      <div class="img mr-2 flex-grow-0 flex-shrink-0 align-self-baseline">
        <Avatar
          :url="post.author.avatar"
          :size="50"
          :rounded="true"
          class="member-pic img"
          :sleep-status="post.author.sleepStatus"
        />
      </div>

      <div class="msg ml-1">
        <Markdown :source="post.body" />
      </div>
    </div>
  </li>
</template>

<script>
import Avatar from '@/components/Avatar'
import Markdown from '@/components/Markdown/Markdown'

export default {
  components: { Avatar, Markdown },
  props: {
    post: { type: Object, default: () => {} },
    mayDeleteEverything: { type: Boolean, default: false }
  }
}
</script>

<style lang="scss" scoped>
.posts .post {
  vertical-align: top;
  padding: 0.75rem 0.5rem;
  position: relative;
  border-top: 1px solid var(--border);

  .metadata {
    margin-top: -0.25rem;
    margin-bottom: 0.25rem;
    font-size: smaller;
    color: var(--dark);

    .author a {
      color: var(--secondary);
    }
  }
}

.content .msg {
  overflow: hidden;
  overflow-wrap: break-word;
  word-break: break-word;
  font-size: 0.9rem;

  // Markdown renderer has funny ideas about font size
  div,
  ::v-deep p,
  ::v-deep ul,
  ::v-deep ol {
    font-size: inherit;
  }

  // Markdown renderer has funny ideas about spacing
  ::v-deep p,
  ::v-deep ol,
  ::v-deep ul {
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;

    &:first-child { margin-top: 0; }
    &:last-child { margin-bottom: 0; }
  }
  ::v-deep ol,
  ::v-deep ul {
    margin-left: 1.5rem;
  }

  // Display quotes as more distinct
  ::v-deep blockquote {
    border-left: 3px solid var(--border);
    padding-left: 1rem;
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
  }

  ::v-deep pre {
    background: none;
  }
}
</style>
