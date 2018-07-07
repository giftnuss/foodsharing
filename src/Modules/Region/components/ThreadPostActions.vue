<template>
  <div class="bootstrap">

      <!-- emoji buttons & selector -->
      <span class="emojis">
        <span v-for="emoji in givenEmojis" :key="emoji.key" v-if="emoji.count">
            <a 
                :class="['btn', 'btn-sm', (emoji.mine ? 'btn-primary' : 'btn-secondary')]"
                :title="emoji.key"
                @click="toggleEmoji(emoji.key)"
            >
                {{ emoji.count }}x <Emoji :name="emoji.key" />
            </a>
        </span>
        <b-dropdown 
            text="➕"
            class="emoji-dropdown"
            toggle-class="btn-outline-secondary"
            size="sm" 
            no-caret
            right
            ref="emojiSelector"
        >
            <a 
                v-for="(symbol, key) in emojis"
                :key="key"
                @click="giveEmoji(key)"
                class="btn"
            >
                <Emoji :name="key" />
            </a>
        </b-dropdown>
    
    </span>

    <span class="divider text-primary">|</span>


    <!-- non emoji button -->
    <a class="btn btn-sm btn-secondary">{{ 'button.answer'|i18n }}</a>
    <a v-if="mayDeletePost" class="btn btn-sm btn-secondary" v-b-modal.modal1>
        {{ 'forum.delete_post'|i18n }}
    </a>


    <!-- delete confirm modal -->
    <b-modal 
        v-if="mayDeletePost"
        id="modal1"
        :title="'forum.delete_post'|i18n"
        :cancel-title="'button.abort'|i18n"
        :ok-title="'button.yes_i_am_sure'|i18n"
        @ok="$emit('delete')"
    >
        <p class="my-4">{{ 'really_delete'|i18n }}</p>
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
    givenEmojis: {},
    mayDeletePost: {}
  },
  data() {
      return {
          emojis: emojiList,
      }
  },
  methods: {
    toggleEmoji(key, dontRemove=false) {
        let myEmojiKeys = this.givenEmojis.filter(e => e.mine).map(e => e.key)

        if(myEmojiKeys.indexOf(key) !== -1) {
            if(dontRemove) return
            else this.$emit('emojiRemove', key)
        } else {
            this.$emit('emojiAdd', key)
        }
    },
    giveEmoji(key) {
        this.$refs.emojiSelector.hide()
        this.toggleEmoji(key, true)
    },
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
}
</style>
