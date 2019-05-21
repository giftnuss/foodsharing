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
          <template
            v-for="slot in occupiedSlots"
          >
            <TakenSlot
              :profile="slot.profile"
              :confirmed="slot.isConfirmed"
              :allow-leave="slot.profile.id == user.id"
              :allow-kick="isCoordinator"
              :allow-confirm="isCoordinator"
              @leave="$refs.modal_leave.show()"
              @kick="activeSlot = slot; $refs.modal_kick.show()"
              @confirm="$emit('confirm', {date: date, fsId: slot.profile.id})"
            />
          </template>
          <template
            v-for="n in totalSlots-occupiedSlots.length"
          >
            <EmptySlot
              :allow-join="!isUserParticipant && !isInPast"
              :key="n"
              @join="$refs.modal_join.show()"
            />
          </template>
        </ul>
      </b-card-text>
      <b-card-footer>
        <b-btn
          v-b-tooltip.hover
          @click="$emit('add-slot', date)"
          class="btn-sm"
          title="Slot hinzufügen"
        >
          +
        </b-btn>
        <b-btn
          v-b-tooltip.hover
          @click="$emit('remove-slot', date)"
          class="btn-sm"
          title="Slot entfernen"
        >
          -
        </b-btn>
      </b-card-footer>
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
import bBtn from '@b/components/button/button'
import bCard from '@b/components/card/card'
import bCardText from '@b/components/card/card-text'
import bCardFooter from '@b/components/card/card-footer'
import bFormTextarea from '@b/components/form-textarea/form-textarea'
import bModal from '@b/components/modal/modal'
import bTooltip from '@b/directives/tooltip/tooltip'
import TakenSlot from './TakenSlot'
import EmptySlot from './EmptySlot'
import dateFnsCompareAsc from 'date-fns/compare_asc'

export default {
  components: { EmptySlot, TakenSlot, bBtn, bCard, bCardFooter, bCardText, bFormTextarea, bModal },
  directives: { bTooltip },
  props: {
    storeId: {
      type: Number,
      default: null
    },
    date: {
      type: Object,
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
    margin: 2px;
    padding: 4px;
    background-color: #f1e7c9;
    border-radius: 3px;
    cursor: pointer;
  }

  ul.slots li:hover {
    background-color: #533a20;
  }
</style>
