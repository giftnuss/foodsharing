import $ from 'jquery'

import L from 'leaflet'

import 'leaflet.css'

import 'leaflet.awesome-markers'
import 'leaflet.awesome-markers.css'
import 'leaflet.awesome-markers.foodsharing-overrides.css'

import 'leaflet.markercluster'
import 'leaflet.markercluster/dist/MarkerCluster.css'
import 'leaflet.markercluster/dist/MarkerCluster.Default.css'

import AddressPicker from 'typeahead-addresspicker'

import { GOOGLE_API_KEY } from '@/server-data'

export let map
export let clusterGroup
let defaultMarker

$(() => {
  const mapEL = document.getElementById('map')
  if (mapEL) initializeMap(mapEL)
})

export async function initializeMap (el) {
  const mapOptions = $(el).data('options')
  if (!mapOptions) return console.error('map is missing data-options')

  const {
    center,
    zoom = 13,
    searchpanel = false,
    markers = [],
    defaultMarkerOptions
  } = mapOptions

  defaultMarker = L.AwesomeMarkers.icon({
    icon: defaultMarkerOptions.icon,
    markerColor: defaultMarkerOptions.color,
    prefix: defaultMarkerOptions.prefix
  })

  map = L
    .map(el)
    .setView(center, zoom)

  L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Geocoding by <a href="https://google.com">Google</a>, Tiles &copy; Esri 2014'
  }).addTo(map)

  clearCluster()

  if (searchpanel) {
    $.getScript(`https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&key=${GOOGLE_API_KEY}`, () => {
      initializeSearchpanel(searchpanel)
    })
  }

  markers.forEach(addMarker)

  commitCluster()
}

function initializeSearchpanel (searchpanel) {
  const $searchpanel = $('#' + searchpanel)
  const addressPicker = new AddressPicker()

  let marker

  const icon = L.AwesomeMarkers.icon({
    icon: 'smile',
    markerColor: 'orange',
    prefix: 'img'
  })

  $searchpanel.typeahead(null, {
    displayKey: 'description',
    source: addressPicker.ttAdapter()
  })

  addressPicker.bindDefaultTypeaheadEvent($searchpanel)

  $(addressPicker).on('addresspicker:selected', (event, result) => {
    const { placeResult } = result
    const { viewport } = placeResult.geometry
    const latLng = L.latLng(result.lat(), result.lng())

    if (marker) {
      marker.setLatLng(latLng)
    } else {
      marker = L.marker(latLng, { icon }).addTo(map)
    }

    if (viewport) {
      map.fitBounds(L.latLngBounds(
        L.latLng(viewport.getNorthEast().lat(), viewport.getNorthEast().lng()),
        L.latLng(viewport.getSouthWest().lat(), viewport.getSouthWest().lng())
      ))
    } else {
      map.setCenter(latLng)
      map.setZoom(16)
    }
  })
}

export function addMarker ({ id, lat, lng, click, icon = defaultMarker }) {
  clusterGroup.addLayer(L.marker(new L.LatLng(lat, lng), { id, click, icon }))
}

export function clearCluster () {
  if (clusterGroup && map) map.removeLayer(clusterGroup)
  clusterGroup = L.markerClusterGroup()
  clusterGroup.on('click', el => {
    const { click } = el.layer.options
    if (click) click()
  })
}

export function commitCluster () {
  map.addLayer(clusterGroup)
}
