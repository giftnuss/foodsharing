<template>
    <a :href="$url('basket', basket.id)" class="list-group-item list-group-item-action">
        <div class="row">
            <div class="col-2 pl-2">
                <img src="/img/basket.png" />
            </div>
            <div class="col-10">
                <div class="text-truncate"><b>{{ basket.description }}</b></div>
                <small v-if="!basket.requests.length" class="text-muted">
                    Bisher keine Anfragen erhalten
                </small>
                <h5 v-if="basket.requests.length" class="text-muted mb-1 pl-2">angefragt von</h5>
                <div v-if="basket.requests.length" class="requests list-group">
                        <a v-for="req in basket.requests" :key="req.time" href="#" @click="openChat(req.user.id, $event)" class="list-group-item list-group-item-action p-1">
                            <div class="row pl-1 align-items-center">
                                <div class="col-1 text-right pt-1">
                                    <avatar
                                        :url="req.user.avatar"
                                        :size="20"
                                        :sleepStatus="req.user.sleepStatus"
                                    />
                                </div>
                                <div class="col-10 pt-1">
                                   <div class="row">
                                        <h6 class="col-4 text-truncate mb-1">{{ req.user.name }}</h6>
                                        <div class="col-8 text-right text-muted">{{ req.time | dateDistanceInWords }}</div>

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
import Avatar from '@/components/Avatar'
import conv from '@/conv'


export default {
    components: { Avatar },
    props: {
        basket: {
            type: Object,
            default: () => ({})
        }
    },
    methods: {
        openChat(userId, e) {
            e.preventDefault()
            conv.userChat(userId)
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
</style>
