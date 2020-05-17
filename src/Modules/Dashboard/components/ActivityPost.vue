<template>
  <li class="activity-item">
    <span class="i">
      <a
        v-if="data.fs_id"
        :href="$url('profile', data.fs_id)"
      >
        <img
          :src="data.icon"
          width="50"
        >
      </a>
      <a v-else>
        <img
          :src="data.icon"
          width="50"
        >
      </a>
    </span>
    <span class="n">
      <a
        v-if="data.fs_id"
        :href="$url('profile', data.fs_id)"
      >
        {{ data.fs_name }}
      </a>
      <a
        v-if="data.sender_email"
        :href="'/?page=mailbox&show=' + data.mailbox_id"
      >
        {{ data.sender_email }}
      </a>
      <i
        v-if="type != 'friendWall'"
        class="fas fa-angle-right"
      />
      <a
        v-if="type == 'forum'"
        :href="dashboardContentLink"
      >
        {{ data.forum_name }}
      </a>
      <a
        v-else-if="type == 'foodsharepoint'"
        :href="dashboardContentLink"
      >
        {{ data.fsp_name }}
      </a>
      <a
        v-else-if="type == 'event'"
        :href="dashboardContentLink"
      >
        {{ $i18n('dashboard.event_title', {title: data.event_name}) }}
      </a>
      <a
        v-else-if="type == 'mailbox'"
        :href="'/?page=mailbox&show=' + data.mailbox_id"
      >
        {{ data.subject }}
      </a>
      <a
        v-else-if="type == 'store'"
        :href="'/?page=fsbetrieb&id=' + data.store_id"
      >
        {{ data.store_name }}
      </a>
      <small v-if="data.source_name">
        {{ data.source_name }}
      </small>
      <small v-else-if="data.source">
        {{ $i18n(translationKey, [data.source]) }}
      </small>
      <small v-else-if="data.mailbox_name && data.sender_email != data.mailbox_name">
        {{ data.mailbox_name }}
      </small>
    </span>
    <span class="t">
      <span class="txt">
        <span v-if="data.gallery">
          <a
            v-for="img in data.gallery"
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
            class="fas fa-angle-down"
          />
        </a>
      </span>
    </span>
    <span
      v-if="data.quickreply"
      class="qr"
    >
      <img :src="user_avatar">
      <textarea
        v-if="!qrLoading"
        v-model="quickreplyValue"
        name="quickreply"
        class="quickreply"
        placeholder="Schreibe eine Antwort..."
        @keyup.enter="sendQuickreply"
      />
      <span
        v-else
        class="loader"
      >
        <i class="fas fa-spinner fa-spin" />
      </span>
    </span>
    <span class="time">
      <i class="far fa-clock" /> {{ when | dateDistanceInWords }}
      <i class="fas fa-angle-right" /> {{ when | dateFormat('full-short') }}
    </span>
    <span class="c" />
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
  props: {
    type: {
      type: String,
      default: null
    },
    data: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      isTruncatedText: true,
      qrLoading: false,
      user_id: serverData.user.id,
      user_avatar: serverData.user.avatar.mini,
      quickreplyValue: null
    }
  },
  computed: {
    dashboardContentLink () {
      switch (this.type) {
        case 'event':
          return url('event', this.data.event_id)
        case 'foodsharepoint':
          return url('foodsharepoint', this.data.region_id, this.data.fsp_id)
        case 'friendWall':
          return url('profile', this.data.fs_id)
        case 'forum':
          return url('forum', this.data.region_id, (this.data.forum_type === 'botforum'), this.data.forum_topic, this.data.forum_post)
        default:
          return '#'
      }
    },
    isTruncatable () {
      return this.data.desc.split(' ').length > 18
    },
    truncatedText () {
      if (this.isTruncatable && this.isTruncatedText) {
        return this.data.desc.split(' ').splice(0, 12).join(' ') + '...'
      } else {
        return this.data.desc
      }
    },
    translationKey () {
      return 'dashboard.source_' + this.type + (this.data.is_own || this.data.is_bot || '')
    },
    when () {
      return dateFnsParseISO(this.data.time)
    }
  },
  methods: {
    async sendQuickreply (txt) {
      console.log('sending reply', this.quickreplyValue)
      this.qrLoading = true
      await sendQuickreply(this.data.quickreply, this.quickreplyValue).then((x) => { pulseInfo(x.message) })
      this.qrLoading = false
      return true
    }
  }
}
</script>

<style lang="scss" scoped>
.activity-item {
  margin-bottom: 10px;
  background-color: var(--white);
  padding: 10px;
  border-radius: 6px;

  span {
    color: #4a3520;

    a {
      color: var(--fs-green) !important;

      &:hover {
        text-decoration: underline !important;
        color: var(--fs-green) !important;
      }
    }
  }

  span.time {
    margin-left: 58px;
    display: block;
    margin-top: 10px;
    font-size: 10px;
    opacity: 0.8;
  }

  span.qr {
    margin-left: 58px;
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
      border-right: 1px solid var(--white);
      border-top-left-radius: 3px;
      border-bottom-left-radius: 3px;
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

  span.n {
    display: block;
    overflow: hidden;
    font-weight: normal;
    font-size: 13px;
    margin-bottom: 10px;
    text-overflow: unset;
    white-space: inherit;
    overflow-wrap: break-word;

    i.fa {
      display: inline-block;
      width: 11px;
      text-align: center;
    }

    small {
      float: right;
      opacity: 0.8;
      font-size: 12px;
    }
  }

  span.t {
    img {
      float: left;
      padding-right: 10px;
    }

    span.img-txt {
      display: inline;
      vertical-align: bottom;

      span.txt {
        border: 0;
        display: inline;
        padding-left: 0;
      }
    }

    span.txt {
      overflow: hidden;
      text-overflow: unset;
      white-space: normal;
      padding-left: 10px;
      border-left: 2px solid var(--fs-brown);
      margin-bottom: 10px;
      display: block;

      a {
        cursor: pointer;
      }

      p {
        margin: 5px 0;
      }

      /deep/ .markdown {
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
  .activity-item span.qr textarea,
  .activity-item span.qr .loader {
    width: 74.6%;
  }
}
@media (max-width: 400px) {
  .activity-item span.n {
    height: 55px;
  }
  .activity-item span.qr textarea,
  .activity-item span.qr .loader {
    width: 82%;
  }
  .activity-item span.time,
  .activity-item span.qr {
    margin-left: 0px;
  }
  .activity-item span.n small {
    float: none;
    display: block;
  }
}
</style>
