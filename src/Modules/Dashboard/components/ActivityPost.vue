<!-- eslint-disable vue/max-attributes-per-line -->
<template>
  <li class="activity-item">
    <span class="n mb-2">
      <a v-if="fs_id && fs_name" :href="$url('profile', fs_id)">
        {{ fs_name }}
      </a>
      <span v-else-if="fs_id">
        {{ $i18n('dashboard.deleted_user') }}
      </span>
      <a v-else-if="sender_email" :href="dashboardContentLink">
        {{ sender_email }}
      </a>

      <i v-if="type != 'friendWall'" class="fas fa-angle-right" />

      <a :href="dashboardContentLink">
        <span v-if="type != 'friendWall'">
          {{ title }}
        </span>
      </a>

      <small v-if="source">
        {{ $i18n(translationKey, [source]) }}
      </small>
    </span>
    <span class="i">
      <a :href="fs_id ? $url('profile', fs_id) : null">
        <img :src="icon" width="50">
      </a>
    </span>

    <span class="t">
      <span class="txt mb-1 pl-2">
        <span v-if="gallery">
          <a
            v-for="img in gallery"
            :key="img.thumb"
            :href="dashboardContentLink"
          >
            <img :src="img.thumb">
          </a>
        </span>
        <span class="img-text">
          <Markdown :source="truncatedText" />
        </span>
        <a
          v-if="isTruncatable"
          @click="isTruncatedText = !isTruncatedText"
        >
          {{ isTruncatedText ? 'alles zeigen' : 'weniger' }}
          <i
            :class="{ 'fa-rotate-180': !isTruncatedText }"
            class="fas fa-fw fa-angle-down"
          />
        </a>
      </span>
    </span>

    <div v-if="quickreply" class="qr mt-2">
      <img :src="user_avatar">
      <textarea
        v-if="!qrLoading"
        v-model="quickreplyValue"
        name="quickreply"
        class="quickreply"
        placeholder="Schreibe eine Antwort..."
        @keyup.enter="sendQuickreply"
      />
      <span v-else class="loader">
        <i class="fas fa-spinner fa-spin" />
      </span>
    </div>

    <div class="time mt-2">
      <i class="far fa-fw fa-clock" />
      <span> {{ $dateDistanceInWords(when) }} </span>
      <i class="fas fa-fw fa-angle-right" />
      <span> {{ $dateFormat(when, 'full-short') }} </span>
    </div>
  </li>
</template>

<script>
import serverData from '@/server-data'
import { sendQuickreply } from '@/api/dashboard'
import { pulseInfo } from '@/script'
import { url } from '@/urls'
import dateFnsParseISO from 'date-fns/parseISO'
import Markdown from '@/components/Markdown/Markdown'

export default {
  components: { Markdown },
  /* eslint-disable vue/prop-name-casing */
  props: {
    // Shared properties
    time: { type: String, required: true },
    time_ts: { type: Number, required: true },

    type: { type: String, required: true },
    desc: { type: String, default: '' },
    title: { type: String, default: '' },

    icon: { type: String, default: '' },
    source: { type: String, default: '' },
    source_suffix: { type: String, default: '' },
    gallery: { type: Array, default: () => { return [] } },
    quickreply: { type: String, default: '' },

    fs_id: { type: Number, default: null },
    fs_name: { type: String, default: '' },
    region_id: { type: Number, default: null },
    entity_id: { type: Number, default: null },

    // Individual update-type properties for forum posts: ActivityUpdateForum
    forum_post: { type: Number, default: null },
    forum_type: { type: String, default: '' },

    // Individual update-type properties for mailboxes: ActivityUpdateMailbox
    sender_email: { type: String, default: '' },
  },
  /* eslint-enable */
  data () {
    return {
      isTruncatedText: true,
      qrLoading: false,
      user_id: serverData.user.id,
      user_avatar: serverData.user.avatar.mini,
      quickreplyValue: null,
    }
  },
  computed: {
    dashboardContentLink () {
      switch (this.type) {
        case 'event':
          return url('event', this.entity_id)
        case 'foodsharepoint':
          return url('foodsharepoint', this.region_id, this.entity_id)
        case 'friendWall':
          return url('profile', this.fs_id)
        case 'forum':
          return url('forum', this.region_id, (this.forum_type === 'botforum'), this.entity_id, this.forum_post)
        case 'mailbox':
          return url('mailbox', this.entity_id)
        case 'store':
          return url('store', this.entity_id)
        default:
          return '#'
      }
    },
    isTruncatable () {
      return this.desc.split(' ').length > 30
    },
    truncatedText () {
      if (this.isTruncatable && this.isTruncatedText) {
        return this.desc.split(' ').splice(0, 30).join(' ') + ' ...'
      } else {
        return this.desc
      }
    },
    translationKey () {
      return 'dashboard.source_' + this.type + this.source_suffix
    },
    when () {
      return dateFnsParseISO(this.time)
    },
  },
  methods: {
    async sendQuickreply (txt) {
      console.log('sending reply', this.quickreplyValue)
      this.qrLoading = true
      await sendQuickreply(this.quickreply, this.quickreplyValue).then((x) => { pulseInfo(x.message) })
      this.qrLoading = false
      return true
    },
  },
}
</script>

<style lang="scss" scoped>
.activity-item {
  a,
  span a,
  span a > span {
    color: var(--fs-green);
    font-size: 0.875rem;
  }

  .qr,
  .time {
    clear: both;
  }

  .qr {
    margin-left: 25px;
    border-radius: 3px;
    opacity: 0.5;

    &:hover,
    &:focus-within {
      opacity: 1;
    }

    img {
      height: 32px;
      width: 32px;
      margin-right: -35px;
      border-radius: 3px;
    }

    textarea,
    .loader {
      border: 0 none;
      height: 16px;
      margin-left: 36px;
      padding: 8px;
      width: 78.6%;
      border-top-right-radius: 3px;
      border-bottom-right-radius: 3px;
      margin-right: -30px;
      background-color: #f9f9f9;
    }

    textarea {
      overflow: hidden;
      overflow-wrap: break-word;
      resize: none;
      height: 16px;
    }

    .loader {
      background-color: var(--white);
      position: relative;
      text-align: left;
      top: -10px;
    }
  }

  .n {
    display: block;
    overflow: hidden;
    text-overflow: unset;
    white-space: inherit;
    overflow-wrap: break-word;

    i.fas {
      width: 0.75em;
    }

    small {
      float: right;
      font-size: 0.875rem;
      opacity: 0.5;
    }
  }

  .t {
    img {
      float: left;
      padding-right: 10px;
    }

    .txt {
      overflow: hidden;
      text-overflow: unset;
      white-space: normal;
      border-left: 3px solid var(--border);
      display: block;

      a {
        cursor: pointer;
      }

      ::v-deep .markdown {
        p {
          margin: 5px 0;
        }

        ol, ul {
          padding-left: 5px;

          > li {
            display: list-item;
            margin: 0;
          }
        }
      }
    }
  }
}

@media (max-width: 900px) {
  .activity-item .qr textarea,
  .activity-item .qr .loader {
    width: 74.6%;
  }
}
@media (max-width: 400px) {
  .activity-item .n {
    height: 55px;
  }
  .activity-item .qr textarea,
  .activity-item .qr .loader {
    width: 82%;
  }
  .activity-item .time,
  .activity-item .qr {
    margin-left: 0px;
  }
  .activity-item .n small {
    float: none;
    display: block;
  }
}
</style>
