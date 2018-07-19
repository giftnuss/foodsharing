<template>
  <div class="bootstrap">

    <!-- emoji buttons & selector -->
    <span class="emojis">
      <span
        v-for="(users, key) in reactions"
        v-if="users.length"
        :key="key">
        <a
          v-b-tooltip.hover
          :title="concatUsers(users)"
          :class="['btn', 'btn-sm', (gaveIThisReaction(key) ? 'btn-primary' : 'btn-secondary')]"
          @click="toggleReaction(key)"
        >
          {{ users.length }}x <Emoji :name="key" />
        </a>
      </span>
      <b-dropdown
        v-b-tooltip.hover
        ref="emojiSelector"
        title="Eine Reaktion hinzufügen"
        text="➕"
        class="emoji-dropdown"
        size="sm"
        no-caret
        right
      >
        <a
          v-for="(symbol, key) in emojis"
          :key="key"
          class="btn"
          @click="giveEmoji(key)"
        >
          <Emoji :name="key" />
        </a>
      </b-dropdown>

    </span>

    <span :class="{divider: true, textPprimary: true, mobile: isMobile }" />

    <!-- non emoji button -->
    <a
      class="btn btn-sm btn-secondary"
      @click="$emit('reply')">{{ $i18n('button.answer') }}</a>
    <a
      v-b-tooltip.hover
      v-if="mayDelete"
      title="Beitrag löschen"
      class="btn btn-sm btn-secondary"
      @click="$refs.modal.show()">
      <i class="fa fa-trash" />
    </a>

    <!-- <a
      v-if="mayEdit"
      v-b-tooltip.hover
      title="Beitrag bearbeiten"
      class="btn btn-sm btn-secondary"
      @click="$emit('edit')">
      <i class="fa fa-pencil" />
    </a> -->

    <!-- delete confirm modal -->
    <b-modal
      v-if="mayDelete"
      ref="modal"
      :title="$i18n('forum.delete_post')"
      :cancel-title="$i18n('button.abort')"
      :ok-title="$i18n('button.yes_i_am_sure')"
      @ok="$emit('delete')"
    >
      <p>{{ $i18n('really_delete') }}</p>
    </b-modal>
  </div>
</template>

<script>
import bDropdown from '@b/components/dropdown/dropdown'
import bModal from '@b/components/modal/modal'
import bTooltip from '@b/directives/tooltip/tooltip'

import Emoji from '@/components/Emoji'
import emojiList from '@/emojiList.json'
import { user } from '@/server-data'

export default {
  components: { bDropdown, Emoji, bModal },
  directives: { bTooltip },
  props: {
    reactions: {
      type: Object,
      default: () => ({})
    },
    mayDelete: {
      type: Boolean,
      default: false
    },
    isMobile: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      emojis: emojiList
    }
  },
  methods: {
    toggleReaction (key, dontRemove = false) {
      if (this.gaveIThisReaction(key)) {
        if (!dontRemove) this.$emit('reactionRemove', key)
      } else {
        this.$emit('reactionAdd', key)
      }
    },
    giveEmoji (key) {
      this.$refs.emojiSelector.hide()
      this.toggleReaction(key, true)
    },
    gaveIThisReaction (key) {
      if (!this.reactions[key]) return false
      return !!this.reactions[key].find(r => r.id === user.id)
    },
    concatUsers (users) {
      let names = users.map(u => u.id === user.id ? 'Du' : u.name)
      if (names.length === 1) return names[0]

      return names.slice(0, names.length - 1).join(', ') + ' & ' + names[names.length - 1]
    }
  }
}
</script>

<style lang="scss">
.emoji-dropdown > button {
    border-radius: 0.2rem !important;
}
.emoji-dropdown .dropdown-menu {
    padding: 10px;
    a.btn {
        padding: 0;
    }
    .emoji {
        padding: 0 0.3em;
    }
}
</style>

<style lang="scss" scoped>
.emojis {
    line-height: 2.2;
    > span > a {
        color: white !important;
        margin-left: 0.3em;
        padding: 0.05rem 0.5rem;
        span {
            font-size: 1.35em;
        }
    }
}
.divider {
    margin: 0 0.3em;
    opacity: 0.3;
    &::before {
        content: '|';
    }
}
.divider.mobile {
    &::before {
        content: '';
    }
    height: 5px;
    display: block;
}
</style>
