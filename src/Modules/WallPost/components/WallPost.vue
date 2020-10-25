<!-- eslint-disable vue/max-attributes-per-line -->
<template>
  <li
    class="post d-flex flex-column justify-content-between align-items-center"
    :class="[`wallpost-${post.id}`, {'important': isImportant(post)}]"
  >
    <div class="metadata text-muted w-100 mx-1 d-inline-flex">
      <span class="author flex-grow-1 flex-shrink-1 ml-sm-2 mr-1" :class="{'with-padding': !wXS}">
        <a :href="$url('profile', post.author.id)">
          {{ post.author.name }}
        </a>
        <span
          v-if="isManager(post.author.id)"
          v-b-tooltip="$i18n('store.isManager')"
          class="is-manager"
        >
          <i class="fab fa-fw fa-redhat" />
        </span>
      </span>

      <span
        v-b-tooltip="$dateFormat(post.createdAt, 'full-long')"
        class="datetime text-right flex-grow-0 flex-shrink-1"
      >
        <i class="far fa-fw fa-clock" />
        {{ displayedDate }}
      </span>
    </div>

    <div class="content w-100 m-1 flex-grow-0 flex-shrink-0 d-flex">
      <div class="img mr-2 flex-grow-0 flex-shrink-0 align-self-baseline">
        <a
          :href="$url('profile', post.author.id)"
          class="d-inline-block"
        >
          <Avatar
            :url="post.author.avatar"
            :size="50"
            :rounded="true"
            class="member-pic img"
            :sleep-status="post.author.sleepStatus"
          />
        </a>
      </div>

      <div class="msg ml-1">
        <Markdown :source="post.body" />
      </div>
    </div>

    <span
      v-if="canDelete"
      class="moderation"
    >
      <b-button
        v-b-tooltip="$i18n('wall.delete')"
        v-b-modal="`confirmDeletion-${post.id}`"
        href="#delete"
        class="delete text-muted text-decoration-none"
        variant="link"
        size="sm"
      >
        <i class="fas fa-fw fa-trash-alt" />
      </b-button>
    </span>

    <b-modal
      v-if="canDelete"
      :id="`confirmDeletion-${post.id}`"
      modal-class="bootstrap"
      :hide-header="isOwn(post)"
      hide-footer
      centered
      hide-header-close
      @ok="$emit('deletePost', post.id)"
    >
      <template v-slot:modal-header>
        <div v-if="!isOwn(post)" class="alert alert-warning" role="alert">
          <div v-if="!mayDeleteEverything">
            <i class="fas fa-fw fa-info-circle" />
            {{ $i18n('wall.info-somebody-else') }}
          </div>
          <hr v-if="!mayDeleteEverything">
          <div>
            <i class="fas fa-fw fa-archive" />
            {{ $i18n('wall.info-logging') }}
          </div>
        </div>
      </template>

      <template v-slot:default="{ cancel, ok }">
        <strong>
          {{ $i18n('wall.confirm-deletion', { name: post.author.name }) }}
        </strong>
        <blockquote>
          <div class="msg ml-1">
            <Markdown :source="post.body" />
          </div>
        </blockquote>
        <b-button class="float-left my-1" variant="primary" @click="cancel()">
          {{ $i18n('button.cancel') }}
        </b-button>
        <b-button class="float-right my-1" variant="outline-danger" @click="ok()">
          {{ $i18n('button.yes_i_am_sure') }}
        </b-button>
      </template>
    </b-modal>
  </li>
</template>

<script>
import differenceInMonths from 'date-fns/differenceInMonths'
import differenceInCalendarYears from 'date-fns/differenceInCalendarYears'
import serverData from '@/server-data'
import Avatar from '@/components/Avatar'
import Markdown from '@/components/Markdown/Markdown'
import MediaQueryMixin from '@/utils/VueMediaQueryMixin'

export default {
  components: { Avatar, Markdown },
  mixins: [MediaQueryMixin],
  props: {
    post: { type: Object, default: () => {} },
    managers: { type: Array, default: () => [] },
    mayDeleteEverything: { type: Boolean, default: false },
  },
  computed: {
    displayedDate () {
      const createdAt = this.post.createdAt
      if (differenceInCalendarYears(new Date(), createdAt) >= 1) {
        return this.$dateFormat(createdAt, 'full-long')
      } else {
        return this.$dateDistanceInWords(createdAt)
      }
    },
    canDelete () {
      if (!serverData.user.id) return false
      // orga can remove problematic content, see StorePermissions:mayDeleteStoreWallPost
      if (this.mayDeleteEverything) return true
      // own posts can always be removed, see StorePermissions:mayDeleteStoreWallPost
      if (this.isOwn(this.post)) return true

      // managers can clean up posts older than 1 month, see StorePermissions:mayDeleteStoreWallPost
      if (this.isManager(serverData.user.id)) {
        return differenceInMonths(new Date(), this.post.createdAt) >= 1
      } else {
        return false
      }
    },
  },
  methods: {
    isManager (userId) {
      if (!userId) return false
      return (this.managers.indexOf(userId) > -1) // no IE: this.managers.includes(userId)
    },
    isOwn (post) {
      return (post.foodsaverId === serverData.user.id)
    },
    isImportant (post) {
      if (!post || !post.author || !post.author.id) return false
      return this.isManager(post.author.id)
    },
  },
}
</script>

<style lang="scss" scoped>
.posts .post {
  --storewall-padding: 0.4rem;

  vertical-align: top;
  padding: calc(2 * var(--storewall-padding)) var(--storewall-padding);
  position: relative;
  border-top: 1px solid var(--border);
  border-left: 3px solid transparent;
  border-right: 3px solid transparent;

  .metadata {
    margin-top: calc(-1 * var(--storewall-padding));
    margin-bottom: var(--storewall-padding);
    font-size: smaller;
    color: var(--dark);

    .author {
      &.with-padding {
        padding-left: calc(50px + 0.25rem); // avatar width + ml-1 post body alignment
      }

      a {
        color: var(--secondary);
      }
    }
  }

  .moderation {
    position: absolute;
    bottom: 0;
    right: 0;

    .delete:hover {
      color: var(--danger) !important;
    }
  }

  &.important {
    border-left-color: rgba(var(--warning-rgb), 0.75);
    border-right-color: rgba(var(--warning-rgb), 0.75);
  }
}

.modal {
  .alert {
    margin-bottom: 0;
    font-size: 0.9rem;

    hr {
      margin-top: 0.5rem;
      margin-bottom: 0.5rem;
    }
  }
  blockquote {
    margin: 0.5rem;
    margin-left: 0;
    padding-left: 0.5rem;
    border-left: 3px solid var(--border);
  }
}

.content .msg,
.modal .msg {
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
