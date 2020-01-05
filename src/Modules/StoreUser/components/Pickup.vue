<template>
  <div>
    <div class="card pickup">
      <div class="card-body">
        <div class="card-title">
          <div :class="{'text-truncate':true, 'font-weight-bold': isToday}">
            {{ date | dateFormat('full-long') }}
          </div>
          <div
            v-if="isCoordinator && !isInPast"
            class="delete-pickup"
          >
            <button
              v-b-tooltip.hover="$i18n('pickup.delete_title')"
              :class="{'btn btn-sm': true, 'cannot-delete': occupiedSlots.length > 0}"
              @click="occupiedSlots.length > 0 ? $refs.modal_delete_error.show() : $refs.modal_delete.show()"
            >
              <i class="fas fa-times" />
            </button>
          </div>
        </div>
        <p class="card-text">
          <ul class="slots">
            <TakenSlot
              v-for="slot in occupiedSlots"
              :key="slot.profile.id"
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
              @join="$refs.modal_join.show()"
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
    </div>
    <b-modal
      ref="modal_join"
      v-model="showJoinModal"
      :title="$i18n('pickup.join_title_date', {'date': $dateFormat(date, 'full-long')})"
      :cancel-title="$i18n('pickup.join_cancel')"
      :ok-title="$i18n('pickup.join_agree')"
      modal-class="bootstrap"
      header-class="d-flex"
      content-class="pr-3 pt-3"
      @ok="$emit('join', date)"
    >
      {{ $i18n('pickup.really_join_date', {'date': $dateFormat(date, 'full-long')}) }}
    </b-modal>
    <b-modal
      ref="modal_leave"
      :title="$i18n('pickup.really_leave_date_title', {'date': $dateFormat(date, 'full-long')})"
      :cancel-title="$i18n('pickup.leave_pickup_message_team')"
      :ok-title="$i18n('pickup.leave_pickup_ok')"
      modal-class="bootstrap"
      header-class="d-flex"
      content-class="pr-3 pt-3"
      @ok="$emit('leave', date)"
      @cancel="$refs.modal_team_message.show()"
    >
      <p>{{ $i18n('pickup.really_leave_date', {'date': $dateFormat(date, 'full-long')}) }}</p>
    </b-modal>
    <b-modal
      ref="modal_kick"
      :title="$i18n('pickup.really_kick_user_date_title', {'date': $dateFormat(date, 'full-long'), 'name': activeSlot.profile.name})"
      :cancel-title="$i18n('button.abort')"
      :ok-title="$i18n('button.yes_i_am_sure')"
      modal-class="bootstrap"
      header-class="d-flex"
      content-class="pr-3 pt-3"
      @ok="$emit('kick', { 'date': date, 'fsId': activeSlot.profile.id })"
    >
      <p>{{ $i18n('pickup.really_kick_user_date', {'date': $dateFormat(date, 'full-long'), 'name': activeSlot.profile.name}) }}</p>
    </b-modal>
    <b-modal
      ref="modal_team_message"
      :title="$i18n('pickup.leave_team_message_title')"
      :cancel-title="$i18n('button.abort')"
      :ok-title="$i18n('pickup.team_message_send_and_leave')"
      modal-class="bootstrap"
      header-class="d-flex"
      content-class="pr-3 pt-3"
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
      <p>{{ $i18n('pickup.delete_not_empty', {'date': $dateFormat(date, 'full-long')}) }}</p>
    </b-modal>
    <b-modal
      ref="modal_delete"
      :title="$i18n('pickup.delete_title')"
      :cancel-title="$i18n('button.abort')"
      :ok-title="$i18n('delete')"
      modal-class="bootstrap"
      @ok="$emit('delete', date)"
    >
      <p>{{ $i18n('pickup.really_delete_date', {'date': $dateFormat(date, 'full-long')}) }}</p>
    </b-modal>
  </div>
</template>

<script>

import { BFormTextarea, BModal, VBTooltip } from 'bootstrap-vue'
import TakenSlot from './TakenSlot'
import EmptySlot from './EmptySlot'
import dateFnsCompareAsc from 'date-fns/compareAsc'
import dateFnsIsSameDay from 'date-fns/isSameDay'
import dateFnsParseISO from 'date-fns/parseISO'

export default {
  components: { EmptySlot, TakenSlot, BFormTextarea, BModal },
  directives: { VBTooltip },
  props: {
    storeId: {
      type: Number,
      default: null
    },
    date: {
      type: Date,
      default: null
    },
    isAvailable: {
      type: Boolean,
      default: false
    },
    totalSlots: {
      type: Number,
      default: 0
    },
    occupiedSlots: {
      type: Array,
      default: () => []
    },
    isCoordinator: {
      type: Boolean,
      default: false
    },
    user: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      showJoinModal: false,
      teamMessage: this.$i18n('pickup.leave_team_message_template', { date: this.$dateFormat(this.date, 'full-long') }),
      activeSlot: {
        profile: {
          name: '',
          id: null
        }
      }
    }
  },
  computed: {
    isUserParticipant () {
      return this.occupiedSlots.findIndex((e) => {
        return e.profile.id === this.user.id
      }) !== -1
    },
    isInPast () {
      return dateFnsCompareAsc(new Date(), dateFnsParseISO(this.date)) >= 1
    },
    isToday () {
      return dateFnsIsSameDay(dateFnsParseISO(this.date), new Date())
    },
    emptySlots () {
      return Math.max(this.totalSlots - this.occupiedSlots.length, 0)
    }
  }
}
</script>

<style scoped>
  ul.slots {
    display: flex;
    padding: 0;
    margin: 0 0 5px;
    flex-wrap: wrap;
  }

  ul.slots div {
    display: inline-block;
  }

  ul.slots >>> .btn {
    position: initial;
    display: inline-block;
    padding: 2px;
    margin: 2px;
    width: 41px;
    height: 41px;
    color: var(--fs-brown);
    border-color: var(--fs-beige);
    border-width: 3px;
  }
  ul.slots >>> .btn:hover {
    border-color: var(--fs-brown);
  }
  ul.slots >>> .btn:focus {
    box-shadow: none;
  }
  ul.slots >>> .btn.filled {
    overflow: hidden;
  }
  ul.slots >>> .btn.btn-secondary {
    background-color: var(--fs-beige);
  }
  ul.slots >>> .btn[disabled] {
    opacity: 0.5;
    color: var(--fs-brown);
  }
  ul.slots >>> .btn[disabled]:hover {
    border-color: var(--fs-beige);
    cursor: default;
  }
  /* Display deletion button only when hovering pickup date */
  .pickup .delete-pickup {
    display: none;
    position: absolute;
    top: 0;
    right: 0;
  }
  .pickup:hover .delete-pickup {
    display: block;
  }
  .pickup .delete-pickup .btn.cannot-delete {
    color: var(--fs-beige);
  }
</style>
