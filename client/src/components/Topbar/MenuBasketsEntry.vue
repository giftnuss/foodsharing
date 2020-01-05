<template>
  <a
    :href="$url('basket', basket.id)"
    class="list-group-item list-group-item-action"
  >
    <div class="row">
      <div class="col-2 pl-2">
        <img src="/img/basket.png">
      </div>
      <div class="col-10">
        <div class="text-truncate">
          <strong>{{ basket.description }}</strong>
        </div>
        <small
          v-if="!basket.requests.length"
          class="text-muted"
        >
          {{ $i18n('basket.no_requests') }}
        </small>
        <h5
          v-if="basket.requests.length"
          class="text-muted mb-1 pl-2"
        >
          {{ $i18n('basket.requested_by') }}
        </h5>
        <div
          v-if="basket.requests.length"
          class="requests list-group"
        >
          <b-list-group>
            <b-list-group-item
              v-for="req in basket.requests"
              :key="req.id"
              @click.prevent="openChat(req.user.id, $event)"
              href="#"
              class="flex-column align-items-start"
              style="padding: 5px;"
            >
              <div
                class="d-flex w-100 align-items-center"
                style="height:30px;"
              >
                <avatar
                  :url="req.user.avatar"
                  :size="35"
                  :sleep-status="req.user.sleepStatus"
                />
                <div
                  class="d-flex"
                  style="flex-flow:column; margin: 0 auto 0 5px;"
                >
                  <span>{{ req.user.name }}</span>
                  <small>{{ req.time | dateDistanceInWords }}</small>
                </div>
                <a
                  v-b-tooltip
                  @click.prevent.stop="openRemoveDialog(req.user.id, $event)"
                  :title="$i18n('basket.request_close')"
                  href="#"
                  class="m-1 btn btn-sm btn-secondary"
                >
                  <i class="fas fa-times" />
                </a>
              </div>
            </b-list-group-item>
          </b-list-group>
        </div>
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
h5 {
    font-size: 0.8em;
}
.requests {
    h6 {
        font-size: 1em;
        font-weight: bold;

    }
    div {
        font-size: 0.9em;
    }

}
.request .btn {
    padding: 0 0.2rem;
    position: absolute;
    right: 1.2em;
    top: -0.3em;
}
.request .hover {
    display: none;
}

.request:hover .nhover {
    display: none;
}
.request:hover .hover {
    display: block;
}
.nowrap {
    white-space: nowrap;
}
</style>
