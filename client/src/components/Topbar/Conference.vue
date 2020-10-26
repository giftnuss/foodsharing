<script>

import { BModal } from 'bootstrap-vue'

import i18n from '@/i18n'
export default {
  components: { BModal },
  data () {
    return {
      id: null,
    }
  },
  methods: {
    async showConferencePopup (id) {
      const join = await this.$bvModal.msgBoxConfirm(i18n('conference.description_text') + '\n' + i18n('conference.privacy_notice'), {
        modalClass: 'bootstrap',
        title: i18n('conference.join_title'),
        cancelTitle: i18n('button.cancel'),
        okTitle: i18n('conference.join'),
        headerClass: 'd-flex',
        contentClass: 'pr-3 pt-3',
      })
      if (join) {
        this.id = id
        this.join()
      }
    },
    join () {
      window.open(`/api/groups/${this.id}/conference?redirect=true`)
    },
  },
}
</script>
