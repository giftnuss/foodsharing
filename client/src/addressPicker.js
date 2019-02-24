import $ from 'jquery'
import 'corejs-typeahead'
import PhotonAddressEngine from 'typeahead-address-photon'
import L from 'leaflet'
import 'leaflet.awesome-markers'

let fsIcon = L.AwesomeMarkers.icon({
  icon: 'smile',
  markerColor: 'orange',
  prefix: 'img'
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
    let pos = event.target.getLatLng()
    engine.reverseGeocode([pos.lat, pos.lng])
  }).addTo(markers)
  map.fitBounds(markers.getBounds())
}

export function attachAddressPicker () {
  const data = [$('#lat').val(), $('#lon').val()]
  let center = [51, 12]
  let initialZoom = 4
  let map = L.map('map').setView(center, initialZoom)
  setTimeout(() => (map.invalidateSize()), 400)

  L.tileLayer('https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}.png', {
    zoomControl: true,
    maxZoom: 18,
    attribution: 'Geocoding by <a href="https://photon.komoot.de">Komoot Photon</a>, Tiles by <a href="https://foundation.wikimedia.org/w/index.php?title=Maps_Terms_of_Use">Wikimedia</a>'
  }).addTo(map)

  let engine = new PhotonAddressEngine(
    {
      url: 'https://photon.komoot.de',
      formatResult: function (feature) {
        let prop = feature.properties
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
    let prop = selectedPlace.properties
    let geo = selectedPlace.geometry.coordinates
    $('#lat').val(geo[1])
    $('#lon').val(geo[0])
    if (prop.postcode) {
      $('#plz').val(prop.postcode)
    }
    if (prop.city) {
      $('#ort').val(prop.city)
    }
    if (prop.street) {
      $('#anschrift').val(prop.street + (prop.housenumber ? ' ' + prop.housenumber : ''))
    }

    $('#addresspicker').val(selectedPlace.description)
  })

  $('#lat-wrapper,#lon-wrapper').hide()
}
