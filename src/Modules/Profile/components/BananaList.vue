<template>
  <!-- eslint-disable vue/max-attributes-per-line -->
  <div id="bananas" class="popbox bootstrap m-2">
    <h3>
      {{ $i18n('profile.banana.title', { count: (bananaCount ? bananaCount : '') }) }}
    </h3>

    <div v-if="!bananaCount" class="no-banana my-1">
      {{ $i18n('profile.banana.none', { name: recipientName }) }}
    </div>

    <div v-if="canGiveBanana && !hasGivenBanana" class="give-banana mb-2">
      <div v-if="showTextarea">
        <b-alert variant="success" show>
          {{ $i18n('profile.banana.details', { name: recipientName }) }}
          <br>
          <strong>
            {{ $i18n('profile.banana.undo') }}
          </strong>
        </b-alert>
        <b-alert variant="info" show>
          {{ $i18n('profile.banana.vouch') }}
        </b-alert>

        <b-form-textarea
          v-model="bananaText"
          :placeholder="$i18n('profile.banana.placeholder')"
          class="mb-2"
          max-rows="8"
          size="sm"
          :state="canSendBanana ? true : null"
        />

        <div class="d-flex justify-content-between">
          <b-button variant="primary" size="sm" @click="toggleTextarea">
            {{ $i18n('button.cancel') }}
          </b-button>
          <b-button
            class="text-right"
            variant="secondary"
            size="sm"
            :disabled="!canSendBanana"
            @click="trySendBanana"
          >
            {{ $i18n('profile.banana.give', { name: recipientName }) }}
          </b-button>
        </div>
      </div>
      <div v-else>
        <b-button variant="secondary" size="sm" @click="toggleTextarea">
          {{ $i18n('profile.banana.give', { name: recipientName }) }}
        </b-button>
      </div>
    </div>

    <div class="banana-list w-100">
      <BananaListEntry
        v-for="b in bananaList"
        :key="b.id"
        :author-id="b.id"
        :author-name="b.name"
        :avatar="b.photo"
        :created-at="b.createdAt"
        :text="b.msg"
      />
    </div>
  </div>
</template>

<script>
import $ from 'jquery'

import { sendBanana } from '@/api/profile'
import i18n from '@/i18n'
import { pulseError, pulseInfo } from '@/script'
import serverData from '@/server-data'

import BananaListEntry from './BananaListEntry'

export default {
  components: { BananaListEntry },
  props: {
    recipientId: { type: Number, required: true },
    recipientName: { type: String, required: true },
    canGiveBanana: { type: Boolean, default: false },
    bananas: { type: Array, default: () => { return [] } },
  },
  data () {
    return {
      bananaCount: this.bananas.length,
      hasGivenBanana: false,
      bananaList: this.bananas,
      showTextarea: false,
      bananaText: '',
    }
  },
  computed: {
    canSendBanana () {
      return this.bananaText && (this.bananaText.trim().length > 99)
    },
  },
  mounted () {
    $.fancybox.update()
  },
  methods: {
    async trySendBanana () {
      try {
        await sendBanana(this.recipientId, this.bananaText.trim())

        // Fake reactive update by inserting submitted data into the UI
        const fakeBanana = this.getFakeBanana()
        this.bananaList.unshift(fakeBanana)
        this.bananaCount += 1

        // Reset UI and component state
        pulseInfo(i18n('profile.banana.sent'))
        this.bananaText = ''
        this.showTextarea = false
        this.hasGivenBanana = true
        $.fancybox.update()
      } catch (err) {
        if (err.code === 400) {
          pulseError(i18n('profile.banana.messageTooShort'))
        } else {
          console.error(err)
          pulseError(i18n('error_unexpected'))
        }
      }
    },
    toggleTextarea () {
      this.showTextarea = !this.showTextarea
      $.fancybox.update()
    },
    getFakeBanana () {
      let profilePic = serverData.user.avatar['50']
      if (profilePic === '/img/50_q_avatar.png') {
        profilePic = null
      }

      return {
        createdAt: new Date().toISOString(),
        id: serverData.user.id,
        photo: profilePic ? profilePic.substr('50_q_/images/'.length) : profilePic,
        msg: this.bananaText.trim(),
      }
    },
  },
}
</script>

<style lang="scss" scoped>
#bananas {
  min-width: 50vw;
  max-width: 750px;
}
</style>
