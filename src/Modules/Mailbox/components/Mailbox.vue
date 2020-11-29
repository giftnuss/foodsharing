<template>
  <div class="vue-mailbox bootstrap">
    <div class="card rounded">
      <div class="card-header bg-primary text-white">
        <div class="row align-items-center">
          <div class="col font-weight-bolder">
            {{ $i18n('mailbox.title') }}
          </div>
          <div class="col col-3 text-right">
            <b-button
              v-b-tooltip
              :title="$i18n('mailbox.write')"
              class="btn btn-secondary btn-sm write-new ml-1"
              @click="mailboxWrite"
            >
              <i class="fas faw fa-plus" />
            </b-button>
          </div>
        </div>
      </div>
      <div class="card-body">
        <MailboxContent
          v-for="mb in mailboxes"
          :key="mb.id"
          :mailbox-id="mb.id"
          :mailbox-name="mb.name"
          :full-name="fullMailboxName(mb.name)"
          :unread-count="mb.count"
        />
      </div>
    </div>
  </div>
</template>

<script>
/* eslint-disable camelcase */
import { mb_new_message } from '../Mailbox.js'
import MailboxContent from './MailboxContent'

export default {
  components: { MailboxContent },
  props: {
    hostname: { type: String, required: true },
    mailboxes: { type: Array, default: () => { return [] } },
  },
  methods: {
    fullMailboxName (mbname) {
      return mbname + '@' + this.hostname
    },
    mailboxWrite () {
      mb_new_message() // Legacy JS callback
    },
  },
}
</script>

<style lang="scss" scoped>
.card-header .row {
  margin-top: -6px;
  margin-bottom: -6px;
  font-weight: bold;
}

::v-deep .write-new {
  margin-right: -8px;
}
</style>
