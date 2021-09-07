<!-- input field that allows searching for users and shows suggestions -->
<template>
  <vue-simple-suggest
    ref="simpleSuggest"
    v-model="user"
    :list="searchUser"
    :max-suggestions="10"
    :min-length="0"
    :debounce="200"
    :filter-by-query="false"
    mode="select"
    :nullable-select="true"
    value-attribute="id"
    display-attribute="value"
    :styles="autoCompleteStyle"
    :controls="controls"
  >
    <input
      type="text"
      class="form-control with-border"
      :placeholder="placeholder"
    >
    <b-button
      v-b-tooltip="buttonTooltip"
      :disabled="!user"
      variant="secondary"
      type="submit"
      size="sm"
      @click.prevent="buttonClicked"
    >
      <i
        class="fas fa-fw"
        :class="buttonIcon"
      />
    </b-button>
  </vue-simple-suggest>
</template>

<script>
import VueSimpleSuggest from 'vue-simple-suggest'
import { searchUser } from '@/api/search'
import { pulseError } from '@/script'
import i18n from '@/i18n'

export default {
  components: { VueSimpleSuggest },
  props: {
    placeholder: { type: String, default: '' },
    buttonIcon: { type: String, required: true },
    buttonTooltip: { type: String, default: '' },
    filter: { type: Function, default: null },
    /**
     * If not null, the search is restricted to this region.
     */
    regionId: { type: Number, default: null },
  },
  data () {
    return {
      user: null,
      loading: false,
      autoCompleteStyle: {
        inputWrapper: 'input-group',
        suggestions: 'position-absolute list-group',
        suggestItem: 'list-group-item',
      },
      controls: {
        selectionUp: [38, 33],
        selectionDown: [40, 34],
        select: [13, 36],
        showList: [40],
        hideList: [27, 35],
      },
    }
  },
  methods: {
    async searchUser (query) {
      this.loading = true

      // requests search results from the server
      let users = []
      if (query.length > 2) {
        try {
          users = await searchUser(query, this.regionId)
          if (this.filter) {
            // let the external function filter by user id
            const filteredIds = users.map(x => x.id).filter(this.filter)
            users = users.filter(x => filteredIds.includes(x.id))
          }
        } catch (e) {
          pulseError(i18n('error_unexpected'))
        }
      } else {
        this.user = null
      }
      this.loading = false

      return users
    },
    buttonClicked () {
      if (this.user) {
        this.$emit('user-selected', this.user.id)
        this.user = null
        this.$refs.simpleSuggest.setText('')
      }
    },
  },
}
</script>

<style lang="scss" scoped>
.vue-simple-suggest::v-deep {
  .input-wrapper {
    border: 0;
    padding: 0;
  }

  .suggestions {
    z-index: 1000;
    margin: 0;
  }

  .suggest-item {
    display: inline-block;
    line-height: 1;
    max-width: 100%;
    text-overflow: ellipsis;
  }

  .suggest-item.hover {
    border: 1px solid var(--fs-green);
    background-color: var(--fs-green);
    color: white;
  }
}

.vue-simple-suggest.focus::v-deep {
  background-color: white !important;
  border: 0;
}

.vue-simple-suggest-enter-active.suggestions,
.vue-simple-suggest-leave-active.suggestions {
  transition: opacity .2s;
}

.vue-simple-suggest-enter.suggestions,
.vue-simple-suggest-leave-to.suggestions {
  opacity: 0 !important;
}
</style>
