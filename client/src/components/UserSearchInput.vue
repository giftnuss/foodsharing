<!-- input that field that allows searching for users and shows suggestions -->
<template>
  <div class="bootstrap">
    <vue-typeahead-bootstrap
      v-model="query"
      input-class="with-border"
      :data="searchResults"
      :serializer="user => user.value"
      :placeholder="placeholder"
      @hit="userId = $event.id"
      @input="delayedSearch"
    >
      <template slot="append">
        <b-button
          v-b-tooltip="buttonTooltip"
          :disabled="userId <= 0"
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
      </template>
    </vue-typeahead-bootstrap>
  </div>
</template>

<script>
import i18n from '@/i18n'
import { searchUser } from '@/api/search'
import { pulseError } from '@/script'
import VueTypeaheadBootstrap from 'vue-typeahead-bootstrap'

export default {
  components: { VueTypeaheadBootstrap },
  props: {
    placeholder: { type: String, default: '' },
    buttonIcon: { type: String, required: true },
    buttonTooltip: { type: String, default: '' },
  },
  data () {
    return {
      query: '',
      searchResults: [],
      userId: -1,
      timeout: null,
      timer: null,
    }
  },
  methods: {
    async delayedSearch () {
      // add 200 ms timeout before searching to avoid too many request
      if (this.timeout) {
        clearTimeout(this.timeout)
        this.timer = null
      }
      this.timeout = setTimeout(() => {
        this.searchForUser()
      }, 200)
    },
    async searchForUser () {
      // requests search results from the server
      const value = this.query
      if (value.length > 2) {
        try {
          this.searchResults = await searchUser(this.query)
        } catch (e) {
          pulseError(i18n('error_unexpected'))
        }
      } else {
        this.userId = -1
      }
    },
    buttonClicked () {
      this.$emit('user-selected', this.userId)
    },
  },
}
</script>
