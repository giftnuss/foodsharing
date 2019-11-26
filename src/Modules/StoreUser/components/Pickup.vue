<template>
  <div>
    <div class="card pickup">
      <div class="card-body">
        <div class="card-title row">
          <div :class="{col: true, 'text-truncate':true, 'font-weight-bold': isToday}">
            {{ date | dateFormat('full-long') }}
          </div>
          <div
            v-if="isCoordinator && !isInPast"
            class="col-2 p-0 remove"
          >
            <button
              v-b-tooltip.hover
              @click="occupiedSlots.length > 0 ? $refs.modal_delete_error.show() : $refs.modal_delete.show()"
              :title="$i18n('pickup.delete_title')"
              class="btn btn-sm p-0"
            >
              <i class="fa fa-times" />
            </button>
          </div>
        </div>
        <p class="card-text clearfix">
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
              @kick="activeSlot = slot; $refs.modal_kick.show()"
              @confirm="$emit('confirm', {date: date, fsId: slot.profile.id})"
            />
            <EmptySlot
              v-for="n in emptySlots"
              :allow-join="!isUserParticipant && isAvailable && n == 1"
              :allow-remove="isCoordinator && n == emptySlots && !isInPast"
              :key="n"
              @join="$refs.modal_join.show()"
              @remove="$emit('remove-slot', date)"
            />
            <li
              v-if="isCoordinator && totalSlots < 10 && !isInPast"
              @click="$emit('add-slot', date)"
            >
              <button
                v-b-tooltip.hover
                :title="$i18n('pickup.slot_add')"
                type="button"
                class="btn secondary"
              >
                <i class="fa fa-plus" />
              </button>
            </li>
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
      @ok="$emit('join', date)"
      modal-class="bootstrap"
      header-class="d-flex"
      content-class="pr-3 pt-3"
    >
      {{ $i18n('pickup.really_join_date', {'date': $dateFormat(date, 'full-long')}) }}
    </b-modal>
    <b-modal
      ref="modal_leave"
      :title="$i18n('pickup.really_leave_date_title', {'date': $dateFormat(date, 'full-long')})"
      :cancel-title="$i18n('pickup.leave_pickup_message_team')"
      :ok-title="$i18n('pickup.leave_pickup_ok')"
      @ok="$emit('leave', date)"
      @cancel="$refs.modal_team_message.show()"
      modal-class="bootstrap"
      header-class="d-flex"
      content-class="pr-3 pt-3"
    >
      <p>{{ $i18n('pickup.really_leave_date', {'date': $dateFormat(date, 'full-long')}) }}</p>
    </b-modal>
    <b-modal
      ref="modal_kick"
      :title="$i18n('pickup.really_kick_user_date_title', {'date': $dateFormat(date, 'full-long'), 'name': activeSlot.profile.name})"
      :cancel-title="$i18n('button.abort')"
      :ok-title="$i18n('button.yes_i_am_sure')"
      @ok="$emit('kick', { 'date': date, 'fsId': activeSlot.profile.id })"
      modal-class="bootstrap"
      header-class="d-flex"
      content-class="pr-3 pt-3"
    >
      <p>{{ $i18n('pickup.really_kick_user_date', {'date': $dateFormat(date, 'full-long'), 'name': activeSlot.profile.name}) }}</p>
    </b-modal>
    <b-modal
      ref="modal_team_message"
      :title="$i18n('pickup.leave_team_message_title')"
      :cancel-title="$i18n('button.abort')"
      :ok-title="$i18n('pickup.team_message_send_and_leave')"
      @ok="$emit('team-message', teamMessage); $emit('leave', date)"
      modal-class="bootstrap"
      header-class="d-flex"
      content-class="pr-3 pt-3"
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
      @ok="$emit('delete', date)"
      modal-class="bootstrap"
    >
      <p>{{ $i18n('pickup.really_delete_date', {'date': $dateFormat(date, 'full-long')}) }}</p>
    </b-modal>
  </div>
</template>

<script>

import { BFormTextarea, BModal, VBTooltip } from 'bootstrap-vue'
import TakenSlot from './TakenSlot'
import EmptySlot from './EmptySlot'
import dateFnsCompareAsc from 'date-fns/compare_asc'
import isSameDay from 'date-fns/is_same_day'

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
      return dateFnsCompareAsc(new Date(), this.date) >= 1
    },
    isToday () {
      return isSameDay(this.date, new Date())
    },
    emptySlots () {
      return Math.max(this.totalSlots - this.occupiedSlots.length, 0)
    }
  }
}
</script>

<style scoped>
  ul.slots {
    padding: 0;
    margin: 0 0 5px;
    float: left;
    list-style: none;
  }

  ul.slots li {
    float: left;
  }

  ul.slots >>> .btn {
    display: inline-block;
    padding: 4px;
    margin: 2px;
    width: 35px;
    height: 35px;
    border-color: var(--fs-beige);
    border-width: 3px;
  }
  ul.slots >>> .btn.filled {
    overflow: hidden;
  }
  ul.slots >>> .btn.secondary {
    opacity: 0.3;
  }
  ul.slots >>> .btn:hover {
      border-color: #533a20
  }
  ul.slots >>> .btn[disabled]:hover {
      border-color: var(--fs-beige);
      cursor: default;
  }
  ul.slots[data-v-1dfadebe] .btn.secondary {
    opacity: .6;
  }
  .pickup .remove {
    display: none;
    margin-top: -0.1rem;
  }
  .pickup:hover .remove {
    display: block;
  }
</style>
