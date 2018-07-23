<template>
    <a 
        :href="bell.href"
        :class="classes"
    >
        <div class="row">
            <div class="col-2 icon">
                <a href="#" @click="$emit('remove', bell.id)">
                    <i :class="bell.icon" />
                    <i class="fa fa-close" />
                    <!-- <div :class="['avatar', 'avatar_'+avatars.length]">
                        <div v-for="avatar in avatars" :key="avatar" :style="{backgroundImage: `url('${avatar}')`}" />
                    </div> -->
                </a>
            </div>
            <div class="col-10">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">{{ $i18n('bell.'+bell.key+'_title', bell.payload) }}</h5>
                    <small class="text-muted text-right">{{ bell.createdAt | dateDistanceInWords }}</small>
                </div>
                <p class="mb-1 text-truncate">{{ $i18n('bell.'+bell.key, bell.payload) }}</p>
            </div>
        </div>
    </a>
</template>
<script>
// import serverData from '@/server-data'

export default {
    props: {
        bell: {
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
                !this.bell.isRead ? 'list-group-item-warning' :null,
                this.bell.isDeleting ? 'disabledLoading' :null
            ]
        }
    },
    // method
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


// .avatar {
//     height: 3em;
//     width: 3em;
//     line-height: 0.7em;
//     margin-left: -0.5em;
//     div {
//         background-size: cover;
//         background-position: center;
//         display: inline-block;
//     }
// }
.icon {
    font-size: 2em;
    margin-top: 0.2em;
}
.fa {
    display: block;
}
.fa.fa-close {
    display: none;
}
.list-group-item:hover .fa {
    display: none;
}
.list-group-item:hover .fa.fa-close {
    display: block;
}
</style>
