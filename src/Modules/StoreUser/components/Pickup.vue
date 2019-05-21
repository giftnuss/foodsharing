<template>
  <div>
    <b-card
      :title="date | dateFormat('full-long')"
      title-tag="span"
    >
      <b-card-text
        class="clearfix"
      >
        <ul class="slots">
          <TakenSlot
            v-for="slot in occupiedSlots"
            :key="slot.profile.id"
            :profile="slot.profile"
            :confirmed="slot.isConfirmed"
            :allow-leave="slot.profile.id == user.id"
            :allow-kick="isCoordinator"
            :allow-confirm="isCoordinator"
            :allow-chat="slot.profile.id !== user.id"
            @leave="$refs.modal_leave.show()"
            @kick="activeSlot = slot; $refs.modal_kick.show()"
            @confirm="$emit('confirm', {date: date, fsId: slot.profile.id})"
          />
          <EmptySlot
            v-for="n in emptySlots"
            :allow-join="!isUserParticipant && !isInPast && n == 1"
            :allow-remove="isCoordinator && n == emptySlots"
            :key="n"
            @join="$refs.modal_join.show()"
            @remove="$emit('remove-slot', date)"
          />
          <li
            v-if="isCoordinator && totalSlots < 10"
            @click="$emit('add-slot', date)"
          >
            <button
              v-b-tooltip.hover
              type="button"
              class="btn secondary"
              title="Slot hinzufügen"
            >
              <i class="fa fa-plus" />
            </button>
          </li>
        </ul>
      </b-card-text>
    </b-card>
    <b-modal
      ref="modal_join"
      v-model="showJoinModal"
      :title="$i18n('pickup.join_title_date', {'date': $dateFormat(date, 'full-long')})"
      :cancel-title="$i18n('pickup.join_cancel')"
      :ok-title="$i18n('pickup.join_agree')"
      @ok="$emit('join', date)"
      ok-variant="secondary"
    >
      {{ $i18n('pickup.really_join_date', {'date': $dateFormat(date, 'full-long')}) }}
    </b-modal>
    <b-modal
      ref="modal_leave"
      :title="$i18n('pickup.really_leave_date_title', {'date': $dateFormat(date, 'full-long')})"
      :cancel-title="$i18n('button.abort')"
      :ok-title="$i18n('pickup.leave_pickup_ok')"
      @ok="$emit('leave', date)"
      cancel-variant="primary"
      ok-variant="secondary"
    >
      <p>{{ $i18n('pickup.really_leave_date', {'date': $dateFormat(date, 'full-long')}) }}</p>
    </b-modal>
    <b-modal
      ref="modal_kick"
      :title="$i18n('pickup.really_kick_user_date_title', {'date': $dateFormat(date, 'full-long'), 'name': activeSlot.profile.name})"
      :cancel-title="$i18n('button.abort')"
      :ok-title="$i18n('button.yes_i_am_sure')"
      @ok="$emit('kick', { 'date': date, 'fsId': activeSlot.profile.id })"
    >
      <p>{{ $i18n('pickup.really_kick_user_date', {'date': $dateFormat(date, 'full-long'), 'name': activeSlot.profile.name}) }}</p>
    </b-modal>
    <b-modal
      ref="modal_team_message"
      :title="$i18n('pickup.leave_team_message_title')"
      :cancel-title="$i18n('button.abort')"
      :ok-title="$i18n('pickup.team_message_send_and_leave')"
      @ok="$emit('team_message', teamMessage); $emit('leave', date)"
    >
      <b-form-textarea
        v-model="teamMessage"
        rows="4"
      />
    </b-modal>
  </div>
</template>

<script>
import bCard from '@b/components/card/card'
import bCardText from '@b/components/card/card-text'
import bFormTextarea from '@b/components/form-textarea/form-textarea'
import bModal from '@b/components/modal/modal'
import bTooltip from '@b/directives/tooltip/tooltip'
import TakenSlot from './TakenSlot'
import EmptySlot from './EmptySlot'
import dateFnsCompareAsc from 'date-fns/compare_asc'

export default {
  components: { EmptySlot, TakenSlot, bCard, bCardText, bFormTextarea, bModal },
  directives: { bTooltip },
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
    emptySlots () {
      return this.totalSlots - this.occupiedSlots.length
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
    border-color: #f1e7c9;
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
      border-color: #f1e7c9;
      cursor: default;
  }
</style>
