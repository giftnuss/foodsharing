<!-- eslint-disable vue/max-attributes-per-line -->
<template>
  <div>
    <div class="pickup">
      <div class="pickup-title">
        <div v-if="storeTitle" class="store-title">
          <strong>{{ storeTitle }}</strong>
        </div>
        <div
          class="pickup-date"
          :class="{'today': isToday, 'past': isInPast, 'soon': isSoon, 'empty': emptySlots > 0, 'coord': isCoordinator}"
        >
          <span>
            {{ $dateFormat(date, 'full-long') }}
          </span>
          <span
            v-if="showRelativeDate"
            class="text-muted"
          >
            ({{ $dateDistanceInWords(date) }})
          </span>
          <div
            v-if="isCoordinator && !isInPast"
            class="delete-pickup"
          >
            <button
              v-b-tooltip.hover="$i18n('pickup.delete_title')"
              class="btn"
              :class="{'cannot-delete': occupiedSlots.length > 0}"
              @click="occupiedSlots.length > 0 ? $refs.modal_delete_error.show() : $refs.modal_delete.show()"
            >
              <i class="fas fa-trash-alt" />
            </button>
          </div>
        </div>
      </div>
      <p class="pickup-text">
        <ul class="slots">
          <TakenSlot
            v-for="slot in occupiedSlots"
            :key="`${slot.date}-${slot.profile.id}`"
            :profile="slot.profile"
            :confirmed="slot.isConfirmed"
            :allow-leave="slot.profile.id == user.id && !isInPast"
            :allow-kick="isCoordinator && !isInPast"
            :allow-confirm="isCoordinator"
            :allow-chat="slot.profile.id !== user.id"
            @leave="$refs.modal_leave.show()"
            @kick="activeSlot = slot, $refs.modal_kick.show()"
            @confirm="$emit('confirm', {date: date, fsId: slot.profile.id})"
          />
          <EmptySlot
            v-for="n in emptySlots"
            :key="n"
            :allow-join="!isUserParticipant && isAvailable && n == 1"
            :allow-remove="isCoordinator && n == emptySlots && !isInPast"
            @join="$refs.modal_join.show(); fetchSameDayPickups()"
            @remove="$emit('remove-slot', date)"
          />
          <div class="add-pickup-slot">
            <button
              v-if="isCoordinator && totalSlots < 10 && !isInPast"
              v-b-tooltip.hover="$i18n('pickup.slot_add')"
              class="btn secondary"
              @click="$emit('add-slot', date)"
            >
              <i class="fas fa-plus" />
            </button>
          </div>
        </ul>
      </p>
    </div>

    <b-modal
      ref="modal_join"
      v-model="showJoinModal"
      :title="$i18n('pickup.join_title_date', slotDate)"
      :cancel-title="$i18n('pickup.join_cancel')"
      :ok-title="$i18n('pickup.join_agree')"
      :ok-disabled="!loadedUserPickups"
      :hide-header-close="true"
      modal-class="bootstrap"
      header-class="d-flex"
      lazy
      @ok="$emit('join', date)"
    >
      <p>{{ $i18n('pickup.really_join_date', slotInfo) }}</p>

      <div v-if="loadedUserPickups && sameDayPickups && sameDayPickups.length">
        <b-alert variant="warning" show>
          {{ $i18n('pickup.same_day_hint', { day: $dateFormat(date, 'day') } ) }}
        </b-alert>
        <b-list-group>
          <b-list-group-item
            v-for="pickup in sameDayPickups"
            :key="`${pickup.storeId}-${pickup.date}`"
            :href="$url('store', pickup.storeId)"
            target="_blank"
            class="font-weight-bolder"
          >
            <i class="fas fa-fw" :class="[pickup.isConfirmed ? 'fa-check-circle text-secondary' : 'fa-clock text-danger']" />
            {{
              $i18n('pickup.same_day_entry', {
                when: $dateFormat(pickup.date, 'time'),
                name: pickup.storeName,
              })
            }}
          </b-list-group-item>
        </b-list-group>
      </div>
      <div v-else-if="!loadedUserPickups">
        <b-alert variant="light" show>
          <i class="fas fa-fw fa-sync fa-spin" />
        </b-alert>
      </div>
    </b-modal>

    <b-modal
      ref="modal_leave"
      :title="$i18n('pickup.really_leave_date_title', slotDate)"
      :cancel-title="$i18n('pickup.leave_pickup_message_team')"
      :ok-title="$i18n('pickup.leave_pickup_ok')"
      :hide-header-close="true"
      modal-class="bootstrap"
      header-class="d-flex"
      @ok="$emit('leave', date)"
      @cancel="$refs.modal_team_message.show()"
    >
      <p>{{ $i18n('pickup.really_leave_date', slotDate) }}</p>
    </b-modal>

    <b-modal
      ref="modal_kick"
      :title="$i18n('pickup.signout_confirm')"
      :cancel-title="$i18n('button.cancel')"
      :ok-title="$i18n('button.yes_i_am_sure')"
      :hide-header-close="true"
      modal-class="bootstrap"
      header-class="d-flex"
      @ok="$emit('kick', { 'date': date, 'fsId': activeSlot.profile.id, 'message': kickMessage })"
    >
      <p>
        {{ $i18n('pickup.really_kick_user_info', slotInfo ) }}
      </p>
      <blockquote>
        <div>{{ $i18n('salutation.3') }} {{ slotInfo['name'] }},</div>
        <div>{{ $i18n('pickup.kick_message', slotInfo) }}</div>
        <b-form-textarea
          v-model="kickMessage"
          :placeholder="$i18n('pickup.kick_message_placeholder')"
          max-rows="4"
        />
        <div>{{ $i18n('pickup.kick_message_footer') }}</div>
      </blockquote>
    </b-modal>

    <b-modal
      ref="modal_team_message"
      :title="$i18n('pickup.leave_team_message_title')"
      :cancel-title="$i18n('button.cancel')"
      :ok-title="$i18n('pickup.team_message_send_and_leave')"
      modal-class="bootstrap"
      header-class="d-flex"
      @ok="$emit('team-message', teamMessage); $emit('leave', date)"
    >
      <b-form-textarea
        v-model="teamMessage"
        rows="4"
      />
    </b-modal>

    <b-modal
      ref="modal_delete_error"
      :title="$i18n('pickup.delete_title')"
      ok-only
      modal-class="bootstrap"
    >
      <p>{{ $i18n('pickup.delete_not_empty', slotDate) }}</p>
    </b-modal>

    <b-modal
      ref="modal_delete"
      :title="$i18n('pickup.delete_title')"
      :cancel-title="$i18n('button.cancel')"
      :ok-title="$i18n('delete')"
      modal-class="bootstrap"
      @ok="$emit('delete', date)"
    >
      <p>{{ $i18n('pickup.really_delete_date', slotDate) }}</p>
    </b-modal>
  </div>
</template>

<script>

import { BFormTextarea, BModal, VBTooltip } from 'bootstrap-vue'
import differenceInDays from 'date-fns/differenceInDays'
import differenceInHours from 'date-fns/differenceInHours'
import isPast from 'date-fns/isPast'

import { listSameDayPickupsForUser } from '@/api/pickups'

import TakenSlot from './TakenSlot'
import EmptySlot from './EmptySlot'

export default {
  components: { EmptySlot, TakenSlot, BFormTextarea, BModal },
  directives: { VBTooltip },
  props: {
    storeId: { type: Number, required: true },
    storeTitle: { type: String, default: '' },
    date: { type: Date, required: true },
    showRelativeDate: { type: Boolean, default: false },
    isAvailable: { type: Boolean, default: false },
    totalSlots: { type: Number, default: 0 },
    occupiedSlots: { type: Array, default: () => [] },
    isCoordinator: { type: Boolean, default: false },
    user: { type: Object, default: () => { return { id: null } } },
  },
  data () {
    return {
      showJoinModal: false,
      activeSlot: {
        profile: {
          name: '',
          id: null,
        },
      },
      loadedUserPickups: false,
      sameDayPickups: [],
      // cannot use slotDate here since it's computed and needs to avoid circular data references:
      teamMessage: this.$i18n('pickup.leave_team_message_template', { date: this.$dateFormat(this.date, 'full-long') }),
      kickMessage: '',
    }
  },
  computed: {
    slotDate () {
      return {
        date: this.$dateFormat(this.date, 'full-long'),
      }
    },
    slotInfo () {
      return {
        date: this.$dateFormat(this.date, 'full-long'),
        storeName: this.storeTitle,
        name: this.activeSlot.profile.name,
      }
    },
    isUserParticipant () {
      return this.occupiedSlots.findIndex((e) => {
        return e.profile.id === this.user.id
      }) !== -1
    },
    isInPast () {
      return isPast(this.date)
    },
    isSoon () {
      return differenceInDays(this.date, new Date()) <= 3
    },
    isToday () {
      return differenceInHours(this.date, new Date()) <= 24
    },
    emptySlots () {
      return Math.max(this.totalSlots - this.occupiedSlots.length, 0)
    },
  },
  methods: {
    async fetchSameDayPickups () {
      this.sameDayPickups = await listSameDayPickupsForUser(this.user.id, this.date)
      this.loadedUserPickups = true
    },
  },
}
</script>

<style lang="scss" scoped>
.pickup {
  position: relative;
}

.pickup-date {
  padding-bottom: 5px;
  font-size: 0.875rem;

  &.today {
    &:not(.past) {
      font-weight: bolder;
    }
  }

  // Pickup marker to explain traffic lights
  &.coord.soon.empty::after {
    float: right;
    margin-right: -5px;
    text-align: right;
    content: "\f12a"; // fa-exclamation
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    color: var(--warning);
  }
  &.coord.soon.empty.today::after {
    color: var(--danger);
  }
  &.coord.past::after {
    content: "" !important;
  }
}

.pickup-block:not(:last-of-type) {
  .pickup-text {
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--border);
  }
}

// The container for one pickup
.pickup {
  .store-title {
    display: none;
  }

  .pickup-title,
  .store-title {
    font-size: inherit;
  }

  .pickup-text {
    margin-left: -10px;
    margin-right: -10px;
    padding-left: 10px;
    padding-right: 10px;
  }

  // The list of slots for one pickup
  ul.slots {
    display: flex;
    padding: 0;
    margin: 0 0 5px;
    flex-wrap: wrap;

    div {
      display: inline-block;
    }

    /deep/ .btn {
      position: initial;
      display: inline-block;
      margin: 2px;
      margin-left: 1px;
      width: 50px;
      height: 50px;
      color: rgba(var(--fs-brown-rgb), 0.75);
      background-color: rgba(var(--fs-white-rgb), 0.5);
      border-color: var(--fs-beige);
      border-width: 2px;

      &:hover {
        border-color: var(--fs-brown);
      }
      &:focus {
        box-shadow: none;
      }
      &.filled {
        overflow: hidden;
        border-width: 0;
      }
      &.btn-secondary {
        background-color: var(--fs-beige);
      }
      &[disabled] {
        opacity: 1;
      }
      &[disabled]:hover {
        border-color: var(--fs-beige);
        cursor: default;
      }
    }
  }

  /* Display deletion button only when hovering pickup date */
  .delete-pickup {
    display: none;
    position: absolute;
    top: -4px;
    right: -9px;
    color: var(--fs-brown);
    background-color: var(--white);
    opacity: 0.9;

    .btn {
      padding: 3px 5px;
      line-height: 1.38;
    }
  }

  &:hover .delete-pickup {
    display: block;
  }

  .soon .delete-pickup {
    right: 1px;
  }
}

.modal-dialog {
  blockquote {
    margin: 0;
    padding-left: 0.5rem;
    border-left: 3px solid var(--border);

    div {
      margin: 0.25rem;
    }

    textarea[wrap="soft"] {
      overflow-y: auto !important;
    }
  }
}
</style>
