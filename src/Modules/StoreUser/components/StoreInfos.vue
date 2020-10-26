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
        <Markdown :source="particularitiesDescription" />
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
import Markdown from '@/components/Markdown/Markdown'

export default {
  components: { Markdown },
  props: {
    particularitiesDescription: {
      type: String,
      default: '',
    },
    collectionQuantity: {
      type: String,
      default: '',
    },
    storeTitle: {
      type: String,
      default: null,
    },
    street: {
      type: String,
      default: '',
    },
    housenumber: {
      type: String,
      default: '',
    },
    postcode: {
      type: String,
      default: '',
    },
    city: {
      type: String,
      default: '',
    },
    lastFetchDate: {
      type: Date,
      default: null,
    },
    press: {
      type: Number,
      default: 0,
    },
  },
  computed: {
    allowedToMentionInPublic () {
      return this.press === 1
    },
  },
  methods: {
    formatLastFetchDate () {
      return format(new Date(this.lastFetchDate), 'dd.MM.yyyy')
    },

    distanceInDays () {
      return differenceInCalendarDays(new Date(), new Date(this.lastFetchDate))
    },
  },
}
</script>

<style lang="scss" scoped>
.store-desc {
  display: inline-block;

  .desc-block {
    max-width: 100%;
    margin-bottom: 5px;
    padding-bottom: 10px;
    /* Global fallback */
    overflow-wrap: break-word;
    /* Safari / Edge compat: */
    word-break: break-word;
    /* Desired behavior: */
    overflow-wrap: anywhere;

    &:last-child {
      padding-bottom: 0;
    }

    /deep/ .markdown {
      hr {
        border: 0;
        border-top: 1px solid var(--border);
      }

      blockquote {
        padding: 4px 8px;
        border-left: 2px solid var(--border);
        background-color: var(--fs-white);
      }
    }
  }

  .desc-block-title {
    padding-top: 4px;
    padding-bottom: 4px;
    margin-bottom: 10px;
    background-color: var(--fs-beige);
    color: var(--fs-brown);
    font-weight: bolder;
    text-align: center;
  }
}
</style>
