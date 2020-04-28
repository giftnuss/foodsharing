import $ from 'jquery'
import 'corejs-typeahead'
import PhotonAddressEngine from 'typeahead-address-photon'
import L from 'leaflet'
import 'leaflet.awesome-markers'
import 'mapbox-gl-leaflet'
import 'mapbox-gl/dist/mapbox-gl.css'
import { initMap } from '@/mapUtils'

const fsIcon = L.AwesomeMarkers.icon({
  icon: 'smile',
  markerColor: 'orange',
  prefix: 'fa'
})

let markers = L.featureGroup()

function showSelected (event, selected, map, engine) {
  map.removeLayer(markers)
  markers = L.featureGroup()
  markers.addTo(map)

  L.marker([
    selected.geometry.coordinates[1],
    selected.geometry.coordinates[0]
  ], {
    icon: fsIcon,
    draggable: true
  }).on('dragend', function (event) {
    const pos = event.target.getLatLng()
    engine.reverseGeocode([pos.lat, pos.lng])
  }).addTo(markers)
  map.fitBounds(markers.getBounds())
}

export function attachAddressPicker () {
  const data = [$('#lat').val(), $('#lon').val()]
  let center = [51, 12]
  const initialZoom = 4
  const map = initMap('map', center, initialZoom, 18)
  setTimeout(() => (map.invalidateSize()), 400)

  const engine = new PhotonAddressEngine(
    {
      url: 'https://photon.komoot.de',
      formatResult: function (feature) {
        const prop = feature.properties
        return [prop.name || '', prop.street, prop.housenumber || '', prop.postcode, prop.city, prop.country].filter(Boolean).join(' ')
      },
      lang: 'de'
    }
  )

  if (data[0] !== '0' || data[1] !== '0') {
    center = data
    showSelected(null, { geometry: { coordinates: [center[1], center[0]] } }, map, engine)
  }

  $('#addresspicker').typeahead(
    {
      highlight: true,
      minLength: 3,
      hint: true
    },
    {
      displayKey: 'description',
      source: engine.ttAdapter()
    })
  engine.bindDefaultTypeaheadEvent($('#addresspicker'))
  $(engine).bind('addresspicker:selected', function (event, selectedPlace) {
    showSelected(event, selectedPlace, map, engine)
    const prop = selectedPlace.properties
    const geo = selectedPlace.geometry.coordinates
    $('#lat').val(geo[1])
    $('#lon').val(geo[0])
    if (prop.postcode) {
      $('#plz').val(prop.postcode)
    }
    if (prop.city) {
      $('#ort').val(prop.city)
    } else if (prop.state === 'Wien' || prop.state === 'Vienna') {
      // Workaround for https://gitlab.com/foodsharing-dev/foodsharing/issues/549
      // The city attribute is not set for addresses in Wien
      $('#ort').val(prop.state)
    }
    if (prop.street) {
      $('#anschrift').val(prop.street + (prop.housenumber ? ' ' + prop.housenumber : ''))
    }

    $('#addresspicker').val(selectedPlace.description)
  })

  $('#lat-wrapper,#lon-wrapper').hide()
}
