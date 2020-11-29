<template>
  <div class="mailbox-content my-2">
    <div
      v-b-tooltip.hover.right
      :title="fullName"
      class="mailbox-name text-monospace d-inline-block pr-2"
    >
      <i :class="['fas fa-fw', unreadCount ? 'fa-envelope' : 'fa-envelope-open-text']" />
      {{ mailboxName }}
    </div>

    <div class="mailbox-folders ml-4">
      <b-link
        :class="{'font-weight-bold': !!unreadCount}"
        @click.prevent="loadMailbox(FOLDER_INBOX)"
      >
        {{ $i18n('mailbox.inbox') }}
        <b-badge
          v-if="!!unreadCount"
          pill
          variant="primary"
        >
          {{ unreadCount }}
        </b-badge>
      </b-link>
      ·
      <b-link @click.prevent="loadMailbox(FOLDER_SENT)">
        {{ $i18n('mailbox.sent') }}
      </b-link>
      ·
      <b-link @click.prevent="loadMailbox(FOLDER_TRASH)">
        {{ $i18n('mailbox.trash') }}
      </b-link>
    </div>
  </div>
</template>

<script>
/* eslint-disable camelcase */
import { ajreq } from '@/script'
import { mb_setMailbox } from '../Mailbox.js'

export default {
  props: {
    mailboxId: { type: Number, required: true },
    mailboxName: { type: String, required: true },
    fullName: { type: String, default: '' },
    unreadCount: { type: Number, default: 0 },
  },
  data () {
    // this legacy data is mapped to MailboxFolder constants in MailboxXhr
    return {
      FOLDER_INBOX: 'inbox',
      FOLDER_SENT: 'sent',
      FOLDER_TRASH: 'trash',
    }
  },
  methods: {
    async loadMailbox (folderId) {
      await ajreq('loadmails', {
        mb: this.mailboxId,
        folder: folderId,
      })
      mb_setMailbox(this.mailboxId)
    },
  },
}
</script>

<style lang="scss" scoped>
.mailbox-name {
  font-size: 0.875rem;
}
.mailbox-name,
.mailbox-folders ::v-deep a {
  color: var(--black);
}
</style>
