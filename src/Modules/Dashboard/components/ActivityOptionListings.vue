<template>
  <div>
    <form
      id="activity-option-form"
      class="pure-form pure-form-stacked"
    >
      <fieldset :class="{disabledLoading: isLoading}">
        <div class="msg-inside info">
          <i class="fas fa-info-circle" />
          <span> {{ $i18n('dashboard.activity_filter_info') }} </span>
        </div>
        <div
          v-for="listing in listings"
          :key="listing.name"
        >
          <h4>{{ listing.name }}</h4>
          <p>
            <label
              v-for="item in listing.items"
              :key="item.id"
              class="pure-checkbox"
            >
              <input
                v-model="item.checked"
                :name="listing.index"
                :value="item.id"
                type="checkbox"
              >
              <img
                v-if="item.imgUrl"
                :src="item.imgUrl"
                class="option-img"
                height="24"
              >
              {{ item.name }}
            </label>
            <label
              v-if="listing.items.length === 0"
              class="info-italic"
            >
              {{ $i18n('dashboard.empty_section', {type: listing.name}) }}
            </label>
          </p>
        </div>
        <a
          class="button"
          @click="saveOptionListings"
        >
          {{ $i18n('dashboard.save_selection') }}
        </a>
        <a
          class="button cancel-button"
          @click="$emit('close')"
        >
          {{ $i18n('button.abort') }}
        </a>
      </fieldset>
    </form>
  </div>
</template>

<script>
import { getOptionListings, saveOptionListings } from '@/api/dashboard'

export default {
  components: {},
  props: {},
  data () {
    return {
      listings: [],
      isLoading: true
    }
  },
  computed: {
    filteredUpdates: function () {
      return this.updates.filter(
        a => this.displayedTypes.indexOf(a.type) !== -1
      )
    }
  },
  async created () {
    this.listings = await getOptionListings()
    this.isLoading = false
  },
  methods: {
    async saveOptionListings () {
      this.isLoading = true
      await saveOptionListings(this.listings)
      this.$emit('close')
      this.isLoading = false
      this.$emit('reloadData')
    }
  }
}
</script>

<style lang="scss" scoped>
h3 {
  margin-bottom: 10px;
}
h4 {
  margin-top: 14px;
}
.option-img {
  border-radius:4px;
  position:relative;
  top:5px;
}
.button {
  float: right;
  cursor: pointer;
  margin-left: 5px;
}
.cancel-button {
  background-color: #999999;
}
.info-italic {
  font-style: italic;
}
</style>
