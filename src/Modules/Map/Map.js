/* eslint-disable camelcase,eqeqeq */

import '@/core'
import '@/globals'

import $ from 'jquery'

import { getBrowserLocation, expose } from '@/utils'
import { GET } from '@/browser'

import { showLoader, hideLoader, goTo, ajreq, sleepmode } from '@/script'

import storage from '@/storage'

import L from 'leaflet'

import 'leaflet.awesome-markers'
import 'leaflet.markercluster'

import './Map.css'

let u_map = null
let markers = null

expose({
  u_map,
  u_init_map,
  u_loadDialog
})

const fsIcon = L.AwesomeMarkers.icon({
  icon: 'smile',
  markerColor: 'orange',
  prefix: 'img'
})
const bkIcon = L.AwesomeMarkers.icon({
  icon: 'basket',
  markerColor: 'green',
  prefix: 'img'
})
const botIcon = L.AwesomeMarkers.icon({
  icon: 'smile',
  markerColor: 'red',
  prefix: 'img'
})
const bIcon = L.AwesomeMarkers.icon({
  icon: 'store',
  markerColor: 'brown',
  prefix: 'img'
})
const fIcon = L.AwesomeMarkers.icon({
  icon: 'recycle',
  markerColor: 'yellow',
  prefix: 'img'
})

const map = {
  initiated: false,
  init: function () {
    storage.setPrefix('map')

    if (storage.get('center') != undefined && storage.get('zoom') != undefined) {
      u_map = L.map('map').setView(storage.get('center'), storage.get('zoom'))
    } else {
      u_map = L.map('map').setView([50.89, 10.13], 6)
    }

    expose({ u_map }) // need to re-expose it as it is just a variable

    L.tileLayer('https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}.png', {
	  attribution: 'Tiles by <a href="https://foundation.wikimedia.org/w/index.php?title=Maps_Terms_of_Use">Wikimedia</a>'
    }).addTo(u_map)

    this.initiated = true

    u_map.on('dragend', function (e) {
      map.updateStorage()
    })

    u_map.on('zoomend', function (e) {
      map.updateStorage()
    })
  },
  initMarker: function (items) {
    $('#map-control .linklist a').removeClass('active')
    if (items == undefined) {
      if ($('#map-control .foodsaver').length > 0) {
        items = ['betriebe']
      } else {
        items = ['fairteiler', 'baskets']
      }

      if (GET('load') == undefined) {
        items = (storage.get('activeItems'))
      }
    }
    for (let i = 0; i < items.length; i++) {
      $(`#map-control .linklist a.${items[i]}`).addClass('active')
    }

    loadMarker(items)
  },
  updateStorage: function () {
    const center = u_map.getCenter()
    const zoom = u_map.getZoom()

    const activeItems = []
    $('#map-control .linklist a.active').each(function () {
      activeItems.push($(this).attr('name'))
    })

    storage.set('center', [center.lat, center.lng])
    storage.set('zoom', zoom)
    storage.set('activeItems', activeItems)
  },
  setView: function (lat, lon, zoom) {
    if (!this.initiated) {
      this.init()
    }
    u_map.setView([lat, lon], zoom, { animation: true })
  }
}

expose({ map })

function u_init_map (lat, lon, zoom) {
  map.init()
  if (lat == undefined && storage.get('center') == undefined) {
    getBrowserLocation(pos => map.setView(pos.lat, pos.lon, 12))
  }
}

function u_loadDialog (purl) {
  $('#b_content').addClass('loading')
  $('#b_content').dialog('option', 'title', 'lade...')
  $('#b_content').dialog('open')
  const pos = $('#topbar .container').offset()
  $('#b_content').parent().css({
    'left': `${pos.left}px`,
    'top': '80px'
  })

  if (purl != undefined) {
    $.ajax({
      url: purl,
      dataType: 'json',
      success: function (data) {
        if (data.status === 1) {
          u_setDialogData(data)
        }
      }
    })
  }
}

function u_setDialogData (data) {
  $('#b_content .inner').html(data.html)
  $('#b_content').dialog('option', 'title', data.betrieb.name)
  $('#b_content').removeClass('loading')
  $('#b_content .lbutton').button()
}

function init_bDialog () {
  $('#b_content').dialog({
    autoOpen: false,
    modal: false,
    draggable: false,
    resizable: false
  })
}

function loadMarker (types, loader) {
  $('#map-options').hide()
  var options = []
  for (let i = 0; i < types.length; i++) {
    if (types[i] == 'betriebe') {
      $('#map-options input:checked').each(function () {
        options[options.length] = $(this).val()
      })
      $('#map-options').show()
    }
  }

  if (loader == undefined) {
    loader = true
  }

  if (loader) {
    showLoader()
  }

  $.ajax({
    url: '/xhr.php?f=loadMarker',
    data: { types: types, options: options },
    dataType: 'json',
    success: function (data) {
      if (data.status == 1) {
        if (markers != null) {
          u_map.removeLayer(markers)
        }

        markers = null

        markers = L.markerClusterGroup({ maxClusterRadius: 50 })
        let url = ''
        markers.on('click', function (el) {
          const fsid = (el.layer.options.id)
          const type = el.layer.options.type

          if (type === 'fs') {
            url = `/xhr.php?f=fsBubble&id=${fsid}`
            showLoader()
          } else if (type === 'bk') {
            ajreq('bubble', { app: 'basket', id: fsid })
          } else if (type === 'b') {
            url = `/xhr.php?f=bBubble&id=${fsid}`
            u_loadDialog()
          } else if (type === 'f') {
            const bid = (el.layer.options.bid)
            goTo(`/?page=fairteiler&sub=ft&bid=${bid}&id=${fsid}`)
          }
          if (url != '') {
            $.ajax({
              url: url,
              dataType: 'json',
              success: function (data) {
                if (data.status === 1) {
                  if (type === 'fs') {
                    const popup = new L.Popup({ offset: new L.Point(1, -35) })
                    popup.setLatLng(el.latlng)
                    popup.setContent(data.html)
                    u_map.openPopup(popup)
                  } else if (type === 'b') {
                    u_setDialogData(data)
                    sleepmode.init()
                  }
                }
              },
              complete: function () {
                if (loader) {
                  hideLoader()
                }
              }
            })
          }
        })

        if (data.baskets != undefined) {
          $('#map-control li a.baskets').addClass('active')
          for (let i = 0; i < data.baskets.length; i++) {
            const a = data.baskets[i]
            const marker = L.marker(new L.LatLng(a.lat, a.lon), { id: a.id, icon: bkIcon, type: 'bk' })
            markers.addLayer(marker)
          }
        }

        if (data.foodsaver != undefined) {
          $('#map-control li a.foodsaver').addClass('active')
          for (let i = 0; i < data.foodsaver.length; i++) {
            const a = data.foodsaver[i]
            const marker = L.marker(new L.LatLng(a.lat, a.lon), { id: a.id, icon: fsIcon, type: 'fs' })
            markers.addLayer(marker)
          }
        }

        if (data.betriebe != undefined) {
          $('#map-control li a.betriebe').addClass('active')
          for (let i = 0; i < data.betriebe.length; i++) {
            const a = data.betriebe[i]
            const marker = L.marker(new L.LatLng(a.lat, a.lon), { id: a.id, icon: bIcon, type: 'b' })

            markers.addLayer(marker)
          }
        }

        if (data.fairteiler != undefined) {
          $('#map-control li a.fairteiler').addClass('active')
          for (let i = 0; i < data.fairteiler.length; i++) {
            const a = data.fairteiler[i]
            const marker = L.marker(new L.LatLng(a.lat, a.lon), {
              id: a.id,
              bid: a.bid,
              icon: fIcon,
              type: 'f'
            })

            markers.addLayer(marker)
          }
        }

        if (data.botschafter != undefined) {
          $('#map-control li a.botschafter').addClass('active')
          for (let i = 0; i < data.botschafter.length; i++) {
            const a = data.botschafter[i]
            const marker = L.marker(new L.LatLng(a.lat, a.lon), { id: a.id, icon: botIcon, type: 'fs' })
            markers.addLayer(marker)
          }
        }
        u_map.addLayer(markers)
      } else if (markers != null) {
        u_map.removeLayer(markers)
      }
    },
    complete: function () {
      hideLoader()
    }
  })
}

$(document).ready(function () {
  showLoader()
  $('#map-control li a').click(function () {
    $(this).toggleClass('active')

    const types = []
    let i = 0
    $('#map-control li a.active').each(function (el) {
      types[i] = $(this).attr('name')
      i++
    })
    loadMarker(types)
    map.updateStorage()
    return false
  })

  $('#map-options input').change(function () {
    if ($(this).val() === 'allebetriebe') {
      $('#map-options input').prop('checked', false)
      $('#map-options input[value=\'allebetriebe\']').prop('checked', true)
    } else {
      $('#map-options input[value=\'allebetriebe\']').prop('checked', false)
    }
    if ($('#map-options input:checked').length === 0) {
      $('#map-options input[value=\'allebetriebe\']').prop('checked', true)
    }

    const types = []
    let i = 0
    $('#map-control li a.active').each(function (el) {
      types[i] = $(this).attr('name')
      i++
    })
    setTimeout(function () {
      loadMarker(types)
    }, 100)
  })

  init_bDialog()
})
