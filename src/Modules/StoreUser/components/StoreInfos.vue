<template>
  <div class="store-desc">
    <div
      v-if="storeTitle != null"
      class="head ui-widget-header ui-corner-top"
    >
      {{ storeTitle }}
    </div>
    <div class="ui-widget ui-widget-content corner-bottom margin-bottom ui-padding">
      <div
        id="inputAdress"
        class="desc-block"
      >
        <div class="desc-block-title ui-widget">
          {{ $i18n('store.address') }}
        </div>
        <div>
          {{ street }} <br>
          {{ postcode }} {{ city }}
        </div>
      </div>
      <div
        id="inputParticularities"
        class="desc-block"
      >
        <div class="desc-block-title ui-widget">
          {{ $i18n('store.particularities') }}
        </div>
        <!-- no whitespace between the tags, since it is formatted as pre-line -->
        <!-- eslint-disable-next-line vue/singleline-html-element-content-newline -->
        <div class="store-particularities">{{ particularitiesDescription }}</div>
      </div>
      <div
        id="inputAverageCollectionQuantity"
        class="desc-block"
      >
        <div class="desc-block-title ui-widget">
          {{ $i18n('store.average_collection_quantity') }}
        </div>
        <div>
          {{ collectionQuantity }}
        </div>
      </div>
      <div
        id="inputAttribution"
        class="desc-block"
      >
        <div class="desc-block-title ui-widget">
          {{ $i18n('store.attribution') }}
        </div>
        <span v-if="allowedToMentionInPublic">{{ $i18n('store.may_referred_to_in_public') }}</span>
        <span v-else>{{ $i18n('store.may_not_referred_to_in_public') }}</span>
      </div>
      <div
        v-if="lastFetchDate !== null"
        id="inputMyLastPickup"
        class="desc-block"
      >
        <div class="desc-block-title ui-widget">
          {{ $i18n('store.my_last_pickup') }}
        </div>
        <span>
          {{ formatLastFetchDate() }}
        </span>
        <span v-if="distanceInDays() > 1">
          ({{ $i18n('store.days_before') }} {{ distanceInDays() }} {{ $i18n('store.days') }})
        </span>
      </div>
    </div>
  </div>
</template>

<script>
import { format } from 'date-fns'
import differenceInCalendarDays from 'date-fns/differenceInCalendarDays'

export default {
  props: {
    particularitiesDescription: {
      type: String,
      default: ''
    },
    collectionQuantity: {
      type: String,
      default: ''
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
    },
    press: {
      type: Number,
      default: 0
    }
  },
  computed: {
    allowedToMentionInPublic () {
      return this.press === 1
    }
  },
  methods: {
    formatLastFetchDate () {
      return format(new Date(this.lastFetchDate), 'dd.MM.yyyy')
    },

    distanceInDays () {
      return differenceInCalendarDays(new Date(), new Date(this.lastFetchDate))
    }
  }
}
</script>

<style lang="scss" scoped>
.store-desc {
  display: inline-block;

  .desc-block {
    max-width: 100%;
    padding-top: 0;
    padding-bottom: 6px;
    margin-bottom: 9px;
    /* Safari / Edge compat: */
    overflow-wrap: break-word;
    /* Desired behavior: */
    overflow-wrap: anywhere;

    &:last-child {
      border-bottom: 0;
      margin-bottom: 0;
    }

    .store-particularities {
      white-space: pre-line;
    }
  }

  .desc-block-title {
    padding-top: 0;
    padding-bottom: 3px;
    font-size: 14px;
    font-weight: bolder;
  }
}
</style>
