<template>
  <div class="field">
    <div
      v-if="storeTitle != null"
      class="head ui-widget-header ui-corner-top"
    >
      {{ storeTitle }}
    </div>
    <div class="ui-widget ui-widget-content corner-bottom margin-bottom ui-padding">
      <div
        id="input-wrapper"
        class="input-wrapper"
      >
        <label
          class="wrapper-label ui-widget"
          for="input"
        > {{ $i18n('store.address') }}</label>
        {{ street }} <br>
        {{ postcode }} {{ city }}
      </div>
      <div
        id="input-wrapper"
        class="input-wrapper"
      >
        <label
          class="wrapper-label ui-widget"
          for="input"
        > {{ $i18n('store.particularities') }}</label>
        {{ particularitiesDescription }}
      </div>
      <div
        id="input-wrapper"
        class="input-wrapper"
      >
        <label
          class="wrapper-label ui-widget"
          for="input"
        > {{ $i18n('store.average_collection_quantity') }}</label>
        {{ collectionQuantity }} {{ $i18n('store.weight') }}
      </div>
      <div
        id="input-wrapper"
        class="input-wrapper"
      >
        <label
          class="wrapper-label ui-widget"
          for="input"
        > {{ $i18n('store.attribution') }}</label>
        <span v-if="press = 1">{{ $i18n('store.may_referred_to_in_public') }}</span>
        <span v-else>{{ $i18n('store.may_not_referred_to_in_public') }}</span>
      </div>
      <div
        v-if="lastFetchDate !=null"
        id="input-wrapper"
        class="input-wrapper"
      >
        <label
          class="wrapper-label ui-widget"
          for="input"
        > {{ $i18n('store.my_last_pickup') }}</label>
        {{ lastFetchDate }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    particularitiesDescription: {
      type: String,
      default: ''
    },
    collectionQuantity: {
      type: Number,
      default: null
    },
    storeTitle: {
      type: String,
      default: null
    },
    street: {
      type: String,
      default: ''
    },
    housenumber: {
      type: String,
      default: ''
    },
    postcode: {
      type: String,
      default: ''
    },
    city: {
      type: String,
      default: ''
    },
    lastFetchDate: {
      type: Date,
      default: null
    }
  },
  data () {
    return {
      press: 0
    }
  }
}
</script>
