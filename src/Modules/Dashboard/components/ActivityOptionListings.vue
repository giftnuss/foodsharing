<template>
  <div>
    <form
      id="activity-option-form"
      class="pure-form pure-form-stacked"
    >
      <fieldset :class="{disabledLoading: isLoading}">
        <h3>Updates Blockieren</h3>
        <div class="msg-inside info">
          <i class="fas fa-info-circle" /> Hier kannst Du einstellen, welche Updates auf Deiner Startseite nicht angezeigt werden sollen.
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
                type="checkbox"
                :name="listing.index"
                :value="item.id"
              >
              <img
                v-if="item.imgUrl"
                class="option-img"
                :src="item.imgUrl"
                height="24"
              >
              {{ item.name }}
            </label>
            <label
              v-if="listing.items.length === 0"
              class="info-italic"
            >
              Du hast noch keine {{ listing.name }}
            </label>
          </p>
        </div>
        <a
          class="button"
          @click="saveOptionListings"
        >
          Einstellungen speichern
        </a>
        <a
          class="button cancel-button"
          @click="$emit('close')"
        >
          Abbrechen
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
