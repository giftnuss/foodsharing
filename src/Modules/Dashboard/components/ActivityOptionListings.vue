<template>
  <div>
    <form id="activity-option-form" class="pure-form pure-form-stacked">
      <fieldset>
        <h3>Updates Blockieren</h3>
        <div class="msg-inside info">
          <i class="fas fa-info-circle"></i> Hier kannst Du einstellen, welche Updates auf Deiner Startseite nicht angezeigt werden sollen.
        </div>
        <div v-for="listing in listings" :key="listing.name">
        <h4>{{listing.name}}</h4>
          <p>
            <label v-for="item in listing.items" :key="item.id" class="pure-checkbox">
              <input type="checkbox" v-model="item.checked" :name="listing.index" :value="item.id">
              <img v-if="item.imgUrl" class="option-img" :src="item.imgUrl" height="24" />
              {{item.name}}
            </label>
          </p>
        </div>
          <a
            v-on:click="saveOptionListings"
            class="button"
            style="float:right;"
          >Einstellungen speichern</a>
      </fieldset>
    </form>
  </div>
</template>

<script>
import { getOptionListings, saveOptionListings } from "@/api/dashboard";

export default {
  components: {},
  props: {},
  data() {
    return {
      listings: []
    };
  },
  async created() {
    this.listings = await getOptionListings();
    console.log(this.listings);
  },
  methods: {
    async saveOptionListings() {
      var res = await saveOptionListings(this.listings);
    }
  },
  computed: {
    filteredUpdates: function() {
      return this.updates.filter(
        a => this.displayedTypes.indexOf(a.type) != -1
      );
    }
  }
};
</script>

<style lang="scss" scoped>
h3 {
  margin-bottom: 10px;
}
.option-img {
  border-radius:4px;
  position:relative;
  top:5px;
}
</style>
