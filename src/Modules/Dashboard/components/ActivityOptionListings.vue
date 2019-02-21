<template>
  <div>
    <form id="activity-option-form" class="pure-form pure-form-stacked">
      <fieldset>
        <h3>Updates Blockieren</h3>
        <div class="msg-inside info">
          <i class="fas fa-info-circle"></i> Hier kannst Du einstellen, welche Updates auf Deiner Startseite nicht angezeigt werden sollen.
        </div>
        <div v-for="listing in listings" :key="listing.index">
        <h4>{{listing.name}}</h4>
          <p>
            <label v-for="item in listing.items" :key="item.id" class="pure-checkbox">
              <input type="checkbox" :checked="item.checked ? 'checked' : ''" :name="listing.index" :value="item.id"> {{item.name}}
            </label>
          </p>
        </div>
          <a
            href="#"
            id="activity-save-option"
            class="button"
            style="float:right;"
          >Einstellungen speichern</a>
      </fieldset>
    </form>
  </div>
</template>

<script>
import { getOptionListings } from "@/api/dashboard";

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
    async infiniteHandler($state) {
      var res = await getUpdates(0);
      if (res.length) {
        this.page += 1;
        res.sort((a, b) => {
          return b.data.time_ts - a.data.time_ts;
        });
        this.updates.push(...res);
        $state.loaded();
      } else {
        $state.complete();
      }
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
</style>
