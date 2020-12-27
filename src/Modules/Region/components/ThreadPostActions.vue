<template>
  <div>
    <div class="emojis mb-1 d-inline-block">
      <b-dropdown
        v-if="canGiveEmoji"
        ref="emojiSelector"
        v-b-tooltip.hover
        title="Eine Reaktion hinzufügen"
        text="+"
        class="emoji-dropdown"
        size="sm"
        no-caret
        right
      >
        <a
          v-for="(symbol, key) in emojisToGive"
          :key="key"
          class="btn"
          @click="giveEmoji(key)"
        >
          <Emoji :name="key" />
        </a>
      </b-dropdown>
      <span
        v-for="(users, key) in reactionsWithUsers"
        :key="key"
      >
        <a
          v-b-tooltip.hover
          :title="concatUsers(users)"
          class="btn btn-sm"
          :class="[gaveIThisReaction(key) ? 'btn-primary' : 'btn-secondary']"
          @click="toggleReaction(key)"
        >
          {{ users.length }}x <Emoji :name="key" />
        </a>
      </span>
    </div>

    <span class="divider text-black-50 mx-1" />

    <a
      class="btn btn-sm btn-secondary"
      @click="$emit('reply')"
    >
      {{ $i18n('button.answer') }}
    </a>
    <a
      v-if="mayDelete"
      v-b-tooltip.hover
      title="Beitrag löschen"
      class="btn btn-sm btn-danger"
      @click="$refs.confirmDelete.show()"
    >
      <i class="fas fa-trash-alt" />
    </a>

    <!-- <a
      v-if="mayEdit"
      v-b-tooltip.hover
      title="Beitrag bearbeiten"
      class="btn btn-sm btn-secondary"
      @click="$emit('edit')">
      <i class="fas fa-pencil-alt" />
    </a> -->

    <!-- delete confirm modal -->
    <b-modal
      v-if="mayDelete"
      ref="confirmDelete"
      :title="$i18n('forum.post.delete')"
      :cancel-title="$i18n('button.cancel')"
      :ok-title="$i18n('button.yes_i_am_sure')"
      cancel-variant="primary"
      ok-variant="outline-danger"
      modal-class="bootstrap"
      @ok="$emit('delete')"
    >
      <p>{{ $i18n('really_delete') }}</p>
    </b-modal>
  </div>
</template>

<script>
import pickBy from 'lodash.pickby'

import { BDropdown, BModal, VBTooltip } from 'bootstrap-vue'

import Emoji from '@/components/Emoji'
import emojiList from '@/emojiList.json'
import { user } from '@/server-data'

export default {
  components: { BDropdown, Emoji, BModal },
  directives: { VBTooltip },
  props: {
    reactions: {
      type: Object,
      default: () => ({}),
    },
    mayDelete: {
      type: Boolean,
      default: false,
    },
  },
  data () {
    return {
      emojis: emojiList,
    }
  },
  computed: {
    reactionsWithUsers () {
      return pickBy(this.reactions, users => users.length > 0)
    },
    canGiveEmoji () {
      return Object.keys(this.emojisToGive).length > 0
    },
    emojisToGive () {
      return pickBy(this.emojis, (symbol, key) => !this.gaveIThisReaction(key))
    },
  },
  methods: {
    toggleReaction (key, dontRemove = false) {
      if (this.gaveIThisReaction(key)) {
        if (!dontRemove) {
          this.$emit('reactionRemove', key)
        }
      } else {
        this.$emit('reactionAdd', key)
      }
    },
    giveEmoji (key) {
      this.$refs.emojiSelector.hide()
      this.toggleReaction(key, true)
    },
    gaveIThisReaction (key) {
      if (!this.reactions[key]) {
        return false
      }
      return !!this.reactions[key].find(r => r.id === user.id)
    },
    concatUsers (users) {
      const names = users.map(u => u.id === user.id ? 'Du' : u.name)
      if (names.length === 1) {
        return names[0]
      }
      return `${names.slice(0, names.length - 1).join(', ')} & ${names[names.length - 1]}`
    },
  },
}
</script>

<style lang="scss" scoped>
.emoji-dropdown {
  .dropdown-menu .btn {
    padding: 0;

    .emoji {
      padding: 0 0.3em;
    }
  }
}

.emojis {
  line-height: 2.5;

  span > a {
    margin-left: 3px;

    span {
      line-height: 1;
      font-size: 1.35em;
      vertical-align: middle;
    }
  }
}

.divider {
  &::before {
    content: '|';
  }
}
</style>
