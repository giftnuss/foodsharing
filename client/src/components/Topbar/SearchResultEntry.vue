<template>
    <a :href="href" class="dropdown-item text-truncate search-result">
        <div v-if="image" class="bg-primary image" :style="{backgroundImage: `url('${image}')`}" />
        {{ title }}
        <small v-if="teaser" class="text-truncate">{{ teaser }}</small>
    </a>
</template>
<script>
export default {
    props: {
        href: {
            type: String
        },
        title: {
            type: String
        },
        teaser: {
            type: String
        },
        image: {
            type: String
        }
    }
}
</script>

<style lang="scss" scoped>
.search-result small {
    display: block;
    margin-top: -0.1em;
}
.image {
    float: left;
    height: 2em;
    width: 2em;
    background-size: cover;
    margin-right: 0.4em;
    margin-top: 0.2em;
}
</style>
