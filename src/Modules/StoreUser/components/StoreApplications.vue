<!-- eslint-disable vue/max-attributes-per-line -->
<template>
  <div class="store-applications bootstrap">
    <b-modal
      id="requests"
      :visible="!!requests.length"
      :title="$i18n('store.request.title', { storeTitle })"
      hide-header-close
      hide-footer
      static
      centered
      scrollable
      @hide="closeHandler"
    >
      <div
        v-for="(r, index) in requests"
        :key="r.id"
        class="request d-flex align-items-center flex-wrap flex-sm-nowrap py-2"
      >
        <a
          v-b-tooltip.hover="$i18n('profile.go')"
          :href="$url('profile', r.id)"
        >
          <Avatar
            :url="r.photo"
            :size="50"
            class="member-pic"
            :sleep-status="r.sleep_status"
          />
        </a>

        <div class="name font-weight-bolder flex-grow-1 mx-3">
          <i
            v-b-tooltip.hover="$i18n('store.request.verified')"
            class="fas fa-fw mr-1"
            :class="{'fa-user-check': r.verified}"
          />
          <a :href="$url('profile', r.id)">
            {{ r.name }}
          </a>
        </div>

        <b-button-group class="request-actions my-1" size="sm">
          <b-button
            variant="secondary"
            @click="acceptRequest(storeId, r.id, index)"
          >
            <i class="fas fa-user-check" /> {{ $i18n('store.request.to-team') }}
          </b-button>
          <b-button
            variant="outline-primary"
            @click="jumperRequest(storeId, r.id, index)"
          >
            <i class="fas fa-user-tag" /> {{ $i18n('store.request.to-jumper') }}
          </b-button>
          <b-button
            v-b-tooltip.hover="$i18n('store.request.to-nowhere')"
            variant="outline-danger"
            @click="denyRequest(storeId, r.id, index)"
          >
            <i class="fas fa-user-times" />
          </b-button>
        </b-button-group>
      </div>
    </b-modal>
  </div>
</template>

<script>
import $ from 'jquery'

import { acceptStoreRequest, removeStoreRequest } from '@/api/stores'
import Avatar from '@/components/Avatar'
import i18n from '@/i18n'
import { hideLoader, showLoader, pulseError, reload } from '@/script'

export default {
  components: { Avatar },
  props: {
    storeId: { type: Number, required: true },
    storeTitle: { type: String, default: '' },
    storeRequests: { type: Array, default: () => [] },
    requestCount: { type: Number, default: 99999 },
  },
  data () {
    return {
      requests: this.storeRequests,
    }
  },
  methods: {
    async acceptRequest (storeId, userId, index) {
      showLoader()
      try {
        await acceptStoreRequest(storeId, userId)
        this.$delete(this.requests, index)
      } catch (e) {
        pulseError(i18n('error_unexpected'))
      } finally {
        hideLoader()
      }
    },
    async jumperRequest (storeId, userId, index) {
      showLoader()
      $.ajax({
        dataType: 'json',
        data: 'fsid=' + userId + '&bid=' + storeId,
        url: '/xhr.php?f=warteRequest',
        success: () => { this.$delete(this.requests, index) },
        complete: () => { hideLoader() },
      })
    },
    async denyRequest (storeId, userId, index) {
      showLoader()
      try {
        await removeStoreRequest(storeId, userId)
        this.$delete(this.requests, index)
      } catch (e) {
        pulseError(i18n('error_unexpected'))
      } finally {
        hideLoader()
      }
    },
    closeHandler () {
      // Refresh the page so the teamlist is queried again
      // (but only do this if at least one request was managed)
      if (this.requests.length < this.requestCount) {
        reload()
      }
    },
  },
}
</script>

<style lang="scss" scoped>
.request-actions .btn {
  white-space: unset;
}

.member-pic ::v-deep img {
  width: 50px;
}

.name a {
  color: var(--secondary);
  font-size: 0.875rem;
}
</style>
