/* eslint-disable eqeqeq */
import '@/core'
import '@/globals'

import $ from 'jquery'

import { ajax, ajreq } from '@/script'
import './Basket.css'

import { addMarker, clearCluster, commitCluster } from '@php/Lib/View/vMap'

import { vueApply, vueRegister } from '@/vue'
import RequestForm from './components/RequestForm'

const mapsearch = {
  lat: null,
  lon: null,
  $basketList: null,

  init: function () {
    this.$basketList = $('#cbasketlist')
  },
  setMarker: function (marker) {
    clearCluster()
    $.each(marker, function (i, marker) {
      addMarker({
        lat: marker.lat,
        lng: marker.lon,
        click: function () {
          mapsearch.loadBasket(marker.id)
        }
      })
    })

    commitCluster()
  },
  loadBasket: function (id) {
    ajreq('bubble', {
      app: 'basket',
      id: id
    })
  },
  fillBasketList: function (baskets) {
    this.$basketList.html('')
    $.each(baskets, function (i, basket) {
      mapsearch.appendList(basket)
    })
    this.$basketList.show('highlight', { color: '#F5F5B5' })
  },
  appendList: function (basket) {
    let img = '/img/basket.png'

    if (basket.picture != '') {
      img = `/images/basket/thumb-${basket.picture}`
    }

    let distance = Math.round(basket.distance)

    if (distance == 1) {
      distance = '1 km'
    } else if (distance < 1) {
      distance = `${distance * 1000} m`
    } else {
      distance = `${distance} km`
    }

    this.$basketList.append(`<li><a class="ui-corner-all" onclick="ajreq('bubble',{app:'basket',id:${basket.id},modal:1});return false;" href="#"><span style="float:left;margin-right:7px;"><img width="35px" src="${img}" class="ui-corner-all"></span><span style="height:35px;overflow:hidden;font-size:11px;line-height:16px;"><strong style="float:right;margin:0 0 0 3px;">(${distance})</strong>${basket.description}</span><span style="clear:both;"></span></a></li>`)
  }
}

mapsearch.init()

if ($('#mapsearch').length > 0) {
  ajax.req('basket', 'basketCoordinates', {
    success: function (ret) {
      if (ret.baskets != undefined && ret.baskets.length > 0) {
        mapsearch.setMarker(ret.baskets)
      }
    }
  })

  $('#map-latLng').on('change', function () {
    console.log()

    ajax.req('basket', 'nearbyBaskets', {
      data: {
        coordinates: JSON.parse($('#map-latLng').val())
      },
      success: function (ret) {
        if (ret.baskets != undefined) {
          mapsearch.fillBasketList(ret.baskets)
        }
      }
    })
  })
}

$(document).ready(() => {
  // Container only exists if the current user is not the basket offerer
  var requestFormContainerId = 'vue-BasketRequestForm'
  if (document.getElementById(requestFormContainerId)) {
    vueRegister({
      RequestForm
    })
    vueApply('#' + requestFormContainerId)
  }
})
