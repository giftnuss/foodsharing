<!-- Leaflet map component that can display vector or tile maps.
  The slot allows adding child components like markers. -->
<template>
  <l-map
    ref="map"
    style="height: 300px"
    :zoom="zoom"
    :center="center"
  >
    <l-tile-layer
      v-if="useVectorMap"
      :options="vectorLayerOptions"
      :tile-layer-class="vectorLayerClass"
      :attribution="attribution"
    />
    <l-tile-layer
      v-else
      :url="tileUrl"
      :attribution="attribution"
    />
    <slot />
  </l-map>
</template>

<script>
import L from 'leaflet'
import { LMap, LTileLayer } from 'vue2-leaflet'
import mapboxgl from 'mapbox-gl'
import 'mapbox-gl-leaflet'
import 'mapbox-gl/dist/mapbox-gl.css'
import 'leaflet/dist/leaflet.css'
import { MAP_ATTRIBUTION, MAP_RASTER_TILES_URL, MAP_TILES_URL } from '@/consts'
import { isWebGLSupported } from '@/utils'

window.mapboxgl = mapboxgl // mapbox-gl-leaflet expects this to be global

export default {
  name: 'LeafletMap',
  components: { LMap, LTileLayer },
  props: {
    zoom: { type: Number, required: true },
    center: { type: Array, required: true },
  },
  data () {
    return {
      attribution: MAP_ATTRIBUTION,
      vectorLayerClass: (url, options) => L.mapboxGL(options),
      vectorLayerOptions: {
        accessToken: 'no-token',
        style: MAP_TILES_URL,
      },
      tileUrl: MAP_RASTER_TILES_URL,
    }
  },
  computed: {
    useVectorMap () {
      return isWebGLSupported()
    },
  },
  methods: {
    /**
     * Returns leaflet's internal map object.
     */
    getMapObject () {
      return this.$refs.map.mapObject
    },
  },
}
</script>

<style scoped>

</style>
