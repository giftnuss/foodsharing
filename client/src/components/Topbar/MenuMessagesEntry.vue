<template>
    <a 
        href="#"
        :class="classes"
        @click="openChat"
    >
        <div class="row">
            <div class="col-2">
                <div :class="['avatar', 'avatar_'+avatars.length]">
                    <div v-for="avatar in avatars" :key="avatar" :style="{backgroundImage: `url('${avatar}')`}" />
                </div>
            </div>
            <div class="col-10">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">{{ conversation.title }}</h5>
                    <small class="text-muted">{{ conversation.lastMessageTime | dateDistanceInWords }}</small>
                </div>
                <p class="mb-1 text-truncate">{{ conversation.lastMessage.bodyRaw }}</p>
            </div>
        </div>
    </a>
</template>
<script>
import serverData from '@/server-data'
import msg from '@/msg'

export default {
    props: {
        conversation: {
            type: Object,
            default: () => ({})
        }
    },
    computed: {
        classes() {
            return [
                'list-group-item',
                'list-group-item-action',
                'flex-column',
                'align-items-start',
                this.conversation.hasUnreadMessages ? 'list-group-item-warning' :null
            ]
        },
        avatars() {
            let lastId = this.conversation.lastMessage.authorId
            let members = this.conversation.members

                // without ourselve
                .filter(m => m.id === this.loggedinUser.id)

                // bring last participant to the top
                .sort( (a,b) => {
                    if(a.id == lastId) return -1
                    if(b.id == lastId) return 1
                    return 0
                })
            // enough avatars for displaying? 
            if(members.filter(m => m.avatar).length > 4) {
                members = members.filter(m => m => m.avatar)
            }

            // we dont need more then 4
            members = members.slice(0, 4)

            return [
                '/images/b3bc0229b900aca4bf7f84b063a2291d.png',
                '/img/130_q_avatar.png',
                '/img/pica_bread.png',
                '/img/pica_foodporn.png'
            ]
            return members.map(m => m.avatar)
        },
        loggedinUser() {
            return serverData.user
        }
    },
    methods: {
        openChat() {
            msg.loadConversation(this.conversation.id)
        }
    }
}
</script>

<style lang="scss" scoped>
h5 {
    font-weight: bold;
    font-size: 1em;
}
p {
    font-size: 0.8em;
}
.list-group-item {
    padding: 0.4em 1em;
}

.avatar {
    height: 3em;
    width: 3em;
    line-height: 0.7em;
    margin-left: -0.5em;
    div {
        background-size: cover;
        background-position: center;
        display: inline-block;
    }
}
.avatar_1 div {
    height: 3em;
    width: 3em;
}
.avatar_2 div {
    height: 3em;
    width: 1.5em;
}
.avatar_3 div, .avatar_4 div {
    height: 1.5em;
    width: 1.5em;
}
</style>
