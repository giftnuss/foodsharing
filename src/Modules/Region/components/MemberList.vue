<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <div
        v-if="isWorkGroup"
        class="card-header text-white bg-primary"
      >
        {{ $i18n('memberlist.header_for_workgroup', {bezirk: regionName}) }}
        <span>
          {{ $i18n('memberlist.some_in_all', {some: membersFiltered.length, all: memberList.length}) }}
        </span>
      </div>
      <div
        v-else
        class="card-header text-white bg-primary"
      >
        {{ $i18n('memberlist.header_for_district', {bezirk: regionName}) }}
        <span>
          {{ $i18n('memberlist.some_in_all', {some: membersFiltered.length, all: memberList.length}) }}
        </span>
      </div>

      <div
        v-if="memberList.length"
        class="card-body p-0"
      >
        <div class="form-row p-1">
          <div
            v-if="managementModeEnabled"
            class="col-11"
          >
            <user-search-input
              id="new-foodsaver-search"
              class="m-1"
              :placeholder="$i18n('search.user_search.placeholder')"
              button-icon="fa-user-plus"
              :button-tooltip="$i18n('group.member_list.add_member')"
              @user-selected="addNewTeamMember"
            />
          </div>
          <div
            v-if="!managementModeEnabled"
            class="col-2 text-center"
          >
            <label class=" col-form-label col-form-label-sm foo">
              {{ $i18n('list.filter_for') }}
            </label>
          </div>
          <div
            v-if="!managementModeEnabled"
            class="col-8"
          >
            <input
              v-model="filterText"
              type="text"
              class="form-control form-control-sm"
              placeholder="Name"
            >
          </div>
          <div
            v-if="!managementModeEnabled"
            class="col"
          >
            <button
              v-b-tooltip.hover
              :title="$i18n('button.clear_filter')"
              type="button"
              class="btn btn-sm"
              @click="clearFilter"
            >
              <i class="fas fa-times" />
            </button>
          </div>
          <div class="col">
            <button
              v-if="mayEditMembers"
              v-b-tooltip.hover.top
              :title="$i18n(managementModeEnabled ? 'group.member_list.admin_mode_off' : 'group.member_list.admin_mode_on')"
              :class="[managementModeEnabled ? ['text-warning', 'active'] : 'text-light', 'btn', 'btn-secondary', 'btn-sm']"
              @click.prevent="toggleManageControls"
            >
              <i class="fas fa-fw fa-cog" />
            </button>
          </div>
        </div>

        <b-table
          :fields="fields"
          :items="membersFiltered"
          :current-page="currentPage"
          :per-page="perPage"
          :sort-compare="compare"
          :busy="isBusy"
          small
          hover
          responsive
          class="foto-table"
        >
          <template #cell(imageUrl)="row">
            <div>
              <img
                :src="row.value"
                :alt="$i18n('terminology.profile_picture')"
                class="user_pic_width"
              >
            </div>
          </template>
          <template #cell(userName)="row">
            <a
              :href="$url('profile', row.item.user.id)"
              :title="row.item.user.id"
            >
              {{ row.item.user.name }}
            </a>
          </template>
          <template
            v-if="isWorkGroup && mayEditMembers && managementModeEnabled"
            #cell(removeButton)="row"
          >
            <b-button
              v-if="userId !== row.item.user.id"
              v-b-tooltip="$i18n('group.member_list.remove_title')"
              size="sm"
              variant="danger"
              :disabled="isBusy"
              @click="showRemoveMemberConfirmation(row.item.user.id, row.item.user.name)"
            >
              <i class="fas fa-fw fa-user-times" />
            </b-button>
          </template>
        </b-table>
        <div class="float-right p-1 pr-3">
          <b-pagination
            v-model="currentPage"
            :total-rows="membersFiltered.length"
            :per-page="perPage"
            class="my-0"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { optimizedCompare } from '@/utils'
import { BButton, BTable, BPagination, VBTooltip } from 'bootstrap-vue'
import { removeMember, addMember } from '@/api/groups'
import { listRegionMembers } from '@/api/regions'
import { hideLoader, pulseError, showLoader } from '@/script'
import i18n from '@/i18n'
import UserSearchInput from '@/components/UserSearchInput'

export default {
  components: { BButton, BTable, BPagination, UserSearchInput },
  directives: { VBTooltip },
  props: {
    userId: { type: Number, default: null },
    groupId: { type: Number, required: true },
    regionName: {
      type: String,
      default: '',
    },
    isWorkGroup: {
      type: Boolean,
      default: false,
    },
    mayEditMembers: { type: Boolean, default: false },
  },
  data () {
    return {
      currentPage: 1,
      perPage: 20,
      filterText: '',
      fields: [
        {
          key: 'imageUrl',
          sortable: false,
          label: '',
          class: 'foto-column',
        }, {
          key: 'userName',
          label: this.$i18n('group.name'),
          sortable: false,
          class: 'align-middle',
        }, {
          key: 'removeButton',
          label: '',
          sortable: false,
          class: 'button-column',
        },
      ],
      memberList: [],
      isBusy: false,
      managementModeEnabled: false,
    }
  },
  computed: {
    membersFiltered () {
      if (!this.filterText.trim()) {
        return this.memberList
      }
      const filterText = this.filterText ? this.filterText.toLowerCase() : null
      return this.memberList.filter((member) => {
        return (
          !filterText || (member.user.name.toLowerCase().indexOf(filterText) !== -1
          )
        )
      })
    },
  },
  async mounted () {
    // fetch the member list from the server
    showLoader()
    this.isBusy = true
    try {
      this.memberList = await listRegionMembers(this.groupId)
    } catch (e) {
      pulseError(i18n('error_unexpected'))
    }
    this.isBusy = false
    hideLoader()
  },
  methods: {
    compare: optimizedCompare,

    clearFilter () {
      this.filterStatus = null
      this.filterText = ''
    },
    async tryRemoveMember (memberId) {
      showLoader()
      this.isBusy = true
      try {
        await removeMember(this.groupId, memberId)
        const index = this.memberList.findIndex(member => member.user.id === memberId)
        if (index >= 0) {
          this.memberList.splice(index, 1)
        }
      } catch (e) {
        pulseError(i18n('error_unexpected'))
      }
      this.isBusy = false
      hideLoader()
    },
    async showRemoveMemberConfirmation (memberId, memberName) {
      const remove = await this.$bvModal.msgBoxConfirm(i18n('group.member_list.remove_text', { name: memberName, id: memberId }), {
        modalClass: 'bootstrap',
        title: i18n('group.member_list.remove_title'),
        cancelTitle: i18n('button.cancel'),
        okTitle: i18n('yes'),
        headerClass: 'd-flex',
        contentClass: 'pr-3 pt-3',
      })
      if (remove) {
        this.tryRemoveMember(memberId)
      }
    },
    toggleManageControls () {
      this.managementModeEnabled = !this.managementModeEnabled
    },
    containsMember (memberId) {
      return this.memberList.find(member => member.user.id === memberId) !== undefined
    },
    async addNewTeamMember (userId) {
      showLoader()
      this.isBusy = true
      try {
        const addedUser = await addMember(this.groupId, userId)

        // the backend doesn't care if the user was already in the group, so we have to check here
        if (!this.containsMember(userId)) {
          // add the user to the local member list
          const userData = { id: addedUser.id, name: addedUser.name, sleep_status: addedUser.sleepStatus }
          this.memberList.push({
            user: userData,
            size: 50,
            imageUrl: addedUser.avatar ?? '/img/50_q_avatar.png',
          })
        }
      } catch (e) {
        pulseError(i18n('error_unexpected'))
      }
      this.isBusy = false
      hideLoader()
    },
  },
}
</script>

<style lang="scss" scoped>
.foto-table /deep/ .foto-column {
  width: 60px;
}

.foto-table /deep/ .button-column {
  width: 50px;
  vertical-align: middle;
  text-align: center;
}
</style>
