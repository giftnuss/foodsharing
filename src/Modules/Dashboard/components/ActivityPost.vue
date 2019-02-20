<template>
  <li class="activity-item">
    <span class="i">
      <a v-if="data.fs_id" :href="'/profile/' + data.fs_id">
        <img :src="data.icon" width="50">
      </a>
      <a v-else>
        <img :src="data.icon" width="50">
      </a>
    </span>
    <span class="n">
      <a v-if="data.fs_id" :href="'/profile/' + data.fs_id">{{ data.fs_name }}</a>
      <i class="fas fa-angle-right"></i>
      <a v-if="type == 'forum'" :href="data.forum_href"> {{ data.forum_name }} </a>
      <a v-else-if="type == 'foodbasket'" :href="'/essenskoerbe/' + data.basked_id"> {{ "Essenskorb #" + data.basked_id }} </a>
      <a v-else-if="type == 'friendWall'" :href="'/profile/' + data.poster_id"> {{ data.poster_name }} </a>
      <a v-else-if="type == 'mailbox'" :href="'/?page=mailbox&show=' + data.mailbox_id"> {{ data.subject }} </a>
      <a v-else-if="type == 'store'" :href="'/?page=fsbetrieb&id=' + data.store_id"> {{ data.store_name }} </a>
      <small v-if="data.region_name">{{ data.region_name }}</small>
      <small v-else-if="data.mailbox_name">{{ data.mailbox_name }}</small>
    </span>
    <span class="t">
      <span class="txt">
        <Markdown :source="truncatedText" />
        <a v-if="isTruncatable" v-on:click="isTxtShortend = !isTxtShortend">
          {{ isTxtShortend ? 'alles zeigen' : 'weniger' }}
          <i class="fas fa-angle-down" :class="{ 'fa-rotate-180': !isTxtShortend }"></i>
        </a>
      </span>
    </span>
    <span class="qr" v-if="data.quickreply">
      <img :src="user_avatar">
      <textarea
        v-if="!qrLoading"
        v-on:keyup.enter="sendQuickreply"
        v-model="quickreplyValue"
        name="quickreply"
        class="quickreply"
        placeholder="Schreibe eine Antwort..."
      ></textarea>
      <span v-else class="loader">
        <i class="fas fa-spinner fa-spin"></i>
      </span>
    </span>
    <span class="time">
      <i class="far fa-clock"></i> 6 minutes ago
      <i class="fas fa-angle-right"></i> 19.02.2019 17.52 Uhr
    </span>
    <span class="c"></span>
  </li>
</template>

<script>
import serverData from '@/server-data'
import { sendQuickreply } from "@/api/dashboard";
import { pulseError, pulseInfo } from '@/script'
import Markdown from '@/components/Markdown/Markdown'

/* TODOs
- text should be markdown
- time should be "from now"
- readd ability to display photos
*/

export default {
  components: {Markdown},
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
      isTxtShortend: true,
      qrLoading: false,
      user_avatar: serverData.user.avatar.mini,
      quickreplyValue: null
    }
  },
  computed: {
    isTruncatable(){
      return this.data.desc.split(" ").length > 18
    },
    truncatedText(){
      if (this.isTruncatable && this.isTxtShortend) {
        return this.data.desc.split(" ").splice(0,12).join(" ")+'...'
      } else {
        return this.data.desc
      }
    }
  },
  methods: {
    async sendQuickreply(txt) {
      console.log('sending reply', this.quickreplyValue)
      this.qrLoading = true
      await sendQuickreply(this.data.quickreply, this.quickreplyValue).then((x) => {pulseInfo(x.message)})
      this.qrLoading = false
      return true
    }
  }
};
</script>

<style lang="scss" scoped>
.activity-item span.time {
  margin-left: 58px;
  display: block;
  margin-top: 10px;
  font-size: 10px;
  opacity: 0.8;
}

.activity-item span.qr {
  margin-left: 58px;
  border-radius: 3px;
  opacity: 0.5;
}
.activity-item span.qr textarea {
  overflow: hidden;
  overflow-wrap: break-word;
  resize: none;
  height: 16px;
}
.activity-item span.qr:focus-within {
  opacity: 1;
}

.activity-item span.qr:hover {
  opacity: 1;
}

.activity-item span.qr img {
  height: 32px;
  width: 32px;
  margin-right: -35px;
  border-right: 1px solid #ffffff;
  border-top-left-radius: 3px;
  border-bottom-left-radius: 3px;
}
.activity-item span.qr textarea,
.activity-item span.qr .loader {
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

.activity-item span.qr .loader {
  background-color: #ffffff;
  position: relative;
  text-align: left;
  top: -10px;
}

.activity-item span.t span.txt {
  overflow: hidden;
  text-overflow: unset;
  white-space: normal;
  padding-left: 10px;
  border-left: 2px solid #4a3520;
  margin-bottom: 10px;
  display: block;
}
.activity-item span.t span.txt a {
  cursor: pointer;
}
.activity-item span.t span.txt p {
  margin: 5px 0 ;
}
.activity-item span {
  color: #4a3520;
}
.activity-item span a {
  color: #46891b !important;
}
.activity-item span.n {
  display: block;
  overflow: hidden;
}
.activity-item span.n i.fa {
  display: inline-block;
  width: 11px;
  text-align: center;
}
.activity-item span.n small {
  float: right;
  opacity: 0.8;
  font-size: 12px;
}
.activity-item span a:hover {
  text-decoration: underline !important;
  color: #46891b !important;
}

.activity-item {
  margin-bottom: 10px;
  background-color: #ffffff;
  padding: 10px;
  border-radius: 6px;
}

.activity-item span.n {
  font-weight: normal;
  font-size: 13px;
  margin-bottom: 10px;
  text-overflow: unset;
  white-space: inherit;
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
