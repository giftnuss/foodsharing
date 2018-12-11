<template>
  <a
    :href="$url('basket', basket.id)"
    class="list-group-item list-group-item-action">
    <div class="row">
      <div class="col-2 pl-2">
        <img src="/img/basket.png" >
      </div>
      <div class="col-10">
        <div class="text-truncate"><b>{{ basket.description }}</b></div>
        <small
          v-if="!basket.requests.length"
          class="text-muted">
          Bisher keine Anfragen erhalten
        </small>
        <h5
          v-if="basket.requests.length"
          class="text-muted mb-1 pl-2">angefragt von</h5>
        <div
          v-if="basket.requests.length"
          class="requests list-group">
          <a
            v-for="req in basket.requests"
            :key="req.time"
            href="#"
            class="list-group-item list-group-item-action p-1 request"
            @click.prevent="openChat(req.user.id, $event)">
            <div class="row pl-1 align-items-center">
              <div class="col-1 text-right pt-1">
                <avatar
                  :url="req.user.avatar"
                  :size="20"
                  :sleep-status="req.user.sleepStatus"
                />
              </div>
              <div class="col-10 pt-1">
                <div class="row">
                  <h6 class="col text-truncate mb-1">{{ req.user.name }}</h6>
                  <div class="col nowrap text-right text-muted nhover">
                    {{ req.time | dateDistanceInWords }}
                  </div>
                  <div class="text-right text-muted hover">
                    <a
                      v-b-tooltip
                      href="#"
                      class="m-1 btn btn-sm btn-secondary"
                      title="Essensanfrage abschließen"
                      @click.prevent.stop="openRemoveDialog(req.user.id, $event)"><i class="fas fa-times" /></a>
                  </div>

                </div>
              </div>
            </div>
          </a>
        </div>
      </div>
    </div>
  </a>
</template>

<script>
import bTooltip from '@b/directives/tooltip/tooltip'
import Avatar from '@/components/Avatar'
import conv from '@/conv'

export default {
  components: { Avatar },
  directives: { bTooltip },
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
