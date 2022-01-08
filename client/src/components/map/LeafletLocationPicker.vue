<!-- Extension of the LeafletMap that contains a single marker for choosing a location.
  The chosen coordinates are emitted in a "coordinates-change" event. -->
<template>
  <leaflet-map
    ref="leafletMap"
    :zoom="zoom"
    :center="coordinates"
  >
    <l-marker
      ref="marker"
      :lat-lng="coordinates"
      :icon="icon"
    />
  </leaflet-map>
</template>

<script>
import LeafletMap from './LeafletMap'
import { LMarker } from 'vue2-leaflet'

export default {
  name: 'LeafletLocationPicker',
  components: { LeafletMap, LMarker },
  props: {
    zoom: { type: Number, required: true },
    coordinates: { type: Array, required: true },
    icon: { type: Object, required: true },
  },
  data () {
    return {
      coords: this.coordinates,
    }
  },
  mounted () {
    // update the marker's location when the map is moved
    const map = this.$refs.leafletMap.getMapObject()
    map.on('move', () => {
      this.coords = map.getCenter()
      this.$refs.marker.setLatLng(this.coords)
      this.$emit('coordinates-change', [this.coords.lat, this.coords.lng])
    })
  },
}
</script>

<style scoped>

</style>
