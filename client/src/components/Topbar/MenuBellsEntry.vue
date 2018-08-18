<template>
    <a 
        :href="bell.href"
        :class="classes"
    >
        <div class="row">
            <div class="col-2 icon-side">
                <a href="#" @click="$emit('remove', bell.id)">
                    <i v-if="bell.icon" :class="'icon '+bell.icon" />
                    <div v-if="bell.image" class="icon"><img :src="bell.image" /></div>
                    <i class="fas fa-times" />
                    <!-- <div :class="['avatar', 'avatar_'+avatars.length]">
                        <div v-for="avatar in avatars" :key="avatar" :style="{backgroundImage: `url('${avatar}')`}" />
                    </div> -->
                </a>
            </div>
            <div class="col-10">
                <div class="mt-1 d-flex w-100 justify-content-between">
                    <h5 class="mb-1">{{ $i18n('bell.'+bell.key+'_title', bell.payload) }}</h5>
                    <small class="text-muted text-right nowrap">{{ bell.createdAt | dateDistanceInWords }}</small>
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
    font-size: 0.9em;
}
p {
    font-size: 0.8em;
}
.list-group-item {
    padding: 0.4em 1em;
}

.icon-side {
    font-size: 2em;
    margin-top: 0.2em;
}
.icon {
    display: block;
    width: 2em;
}
.icon img {
    width: 100%;
}
.fas.fa-times {
    display: none;
}
.list-group-item:hover .icon {
    display: none;
}
.list-group-item:hover .fas.fa-times {
    display: block;
}
.nowrap {
    white-space: nowrap;
}

/* 
  some bells use ".img-store", which shows a white store based on a .png
  because we are on a white background we have to override this with a black one
*/
.img-store {
    font-family: "Font Awesome 5 Free";
    font-style: normal;
    font-weight: 900;
    font-size: 14px;
    font-size: inherit;
    text-rendering: auto;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    background: none;
    &:before {
        content: "\f07a";
    }
}
</style>
