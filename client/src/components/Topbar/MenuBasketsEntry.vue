<template>
  <a
    :href="$url('basket', basket.id)"
    class="list-group-item list-group-item-action"
  >
    <div class="row basket-entry">
      <img
        style="height:100%"
        src="/img/basket.png"
      >
      <div class="basket-info-box">
        <h5 class="basket-title text-truncate">{{ basket.description }}</h5>
        <small
          v-if="!basket.requests.length"
          class="text-muted"
        >
          {{ $i18n('basket.no_requests') }}
        </small>
        <div
          v-if="basket.requests.length"
          class="text-muted"
        >
          {{ $i18n('basket.requested_by') }}
        </div>
        <b-list-group
          v-if="basket.requests.length"
          class="requests"
        >
          <b-list-group-item
            v-for="req in basket.requests"
            :key="req.id"
            href="#"
            class="d-flex w-100 align-items-center food-basket-create-test-class"
            @click.prevent="openChat(req.user.id, $event)"
          >
            <avatar
              :url="req.user.avatar"
              :size="35"
              :sleep-status="req.user.sleepStatus"
            />
            <div class="d-flex flex-column basket-entry-request">
              <span>{{ req.user.name }}</span>
              <small>{{ req.time | dateDistanceInWords }}</small>
            </div>
            <b-button
              v-b-tooltip
              :title="$i18n('basket.request_close')"
              size="sm"
              variant="secondary"
              @click.prevent.stop="openRemoveDialog(req.user.id, $event)"
            >
              <i class="fas fa-times" />
            </b-button>
          </b-list-group-item>
        </b-list-group>
      </div>
    </div>
  </a>
</template>

<script>
import { VBTooltip } from 'bootstrap-vue'
import Avatar from '@/components/Avatar'
import conv from '@/conv'

export default {
  components: { Avatar },
  directives: { VBTooltip },
  props: {
    basket: {
      type: Object,
      default: () => ({})
    }
  },
  methods: {
    openChat (userId, e) {
      conv.userChat(userId)
    },
    openRemoveDialog (userId, e) {
      this.$emit('basketRemove', this.basket.id, userId)
    }
  }
}
</script>

<style lang="scss" scoped>
.basket-entry {
  flex-flow: row;
  .basket-title {
    margin-bottom: 0;
  }
  .basket-info-box {
    margin: 0 10px;
    width: 100%;
    overflow: hidden;
    .list-group-item {
      padding: 5px;
    }
  }
  .basket-entry-request {
    margin: 0 auto 0 5px;
  }
}
.requests div {
  font-size: 0.9em;
}
</style>
