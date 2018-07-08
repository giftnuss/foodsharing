<template>
  <div class="bootstrap">

    <!-- emoji buttons & selector -->
    <span class="emojis">
      <span
        v-for="emoji in reactions"
        v-if="emoji.count"
        :key="emoji.key">
        <a
          :class="['btn', 'btn-sm', (emoji.mine ? 'btn-primary' : 'btn-secondary')]"
          :title="emoji.key"
          @click="toggleReaction(emoji.key)"
        >
          {{ emoji.count }}x <Emoji :name="emoji.key" />
        </a>
      </span>
      <b-dropdown
        ref="emojiSelector"
        text="➕"
        class="emoji-dropdown"
        toggle-class="btn-outline-secondary"
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

    <span class="divider text-primary">|</span>

    <!-- non emoji button -->
    <a class="btn btn-sm btn-secondary">{{ $i18n('button.answer') }}</a>
    <a
      v-if="mayDelete"
      class="btn btn-sm btn-secondary"
      @click="$refs.modal.show()">
      {{ $i18n('forum.delete_post') }}
    </a>

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
import bModalDirective from '@b/directives/modal/modal'
import Emoji from '@/components/Emoji'
import emojiList from '@/emojiList.json'

export default {
  components: { bDropdown, Emoji, bModal },
  directives: { bModal: bModalDirective },
  props: {
    reactions: {
      type: Object,
      default: () => []
    },
    mayDelete: {
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
      let myReactionKeys = this.reactions.filter(e => e.mine).map(e => e.key)

      if (myReactionKeys.indexOf(key) !== -1) {
        if (!dontRemove) this.$emit('reactionRemove', key)
      } else {
        this.$emit('reactionAdd', key)
      }
    },
    giveEmoji (key) {
      this.$refs.emojiSelector.hide()
      this.toggleReaction(key, true)
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
}
</style>
