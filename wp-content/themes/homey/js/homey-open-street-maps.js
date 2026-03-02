(function($){
    "use strict";

    if ( typeof HOMEY_map_vars !== "undefined" ) {

        var homeyMap;
        var mapMarker = '';
        var osm_markers_cluster;
        var propertyMarker;
        var is_mapbox = HOMEY_map_vars.is_mapbox;
        var api_mapbox = HOMEY_map_vars.api_mapbox;
        var userID = HOMEY_map_vars.user_id;
        var total_results = $('#listings_found');
        var ajaxurl = HOMEY_map_vars.admin_url+ 'admin-ajax.php';
        var header_map_cities = HOMEY_map_vars.header_map_city;
        var markerPricePins = HOMEY_map_vars.markerPricePins;
        var pin_cluster = HOMEY_map_vars.pin_cluster;
        var pin_cluster_icon = HOMEY_map_vars.pin_cluster_icon;
        var pin_cluster_zoom = HOMEY_map_vars.pin_cluster_zoom;
        var is_singular_listing = HOMEY_map_vars.is_singular_listing;
        var homey_default_radius = HOMEY_map_vars.homey_default_radius;
        var geo_country_limit = HOMEY_map_vars.geo_country_limit;
        var geocomplete_country = HOMEY_map_vars.geocomplete_country;
        var markerCluster = null;
        var current_marker = 0;
        var homey_map_first_load = 0;
        var markers = new Array();
        var halfmap_ajax_container = $('#homey_halfmap_listings_container');
        var default_lat = HOMEY_map_vars.default_lat;
        var default_lng = HOMEY_map_vars.default_lng;
        var arrive = HOMEY_map_vars.arrive;
        var depart = HOMEY_map_vars.depart;
        var guests = HOMEY_map_vars.guests;
        var pets = HOMEY_map_vars.pets;
        var search_country = HOMEY_map_vars.search_country;
        var search_city = HOMEY_map_vars.search_city;
        var search_area = HOMEY_map_vars.search_area;
        var listing_type = HOMEY_map_vars.listing_type;
        var country = HOMEY_map_vars.country;
        var state = HOMEY_map_vars.state;
        var city = HOMEY_map_vars.city;
        var area = HOMEY_map_vars.area;
        var booking_type = HOMEY_map_vars.booking_type;
        var start_hour = HOMEY_map_vars.start_time;
        var end_hour = HOMEY_map_vars.end_time;
        var min_price = HOMEY_map_vars.min_price;
        var max_price = HOMEY_map_vars.max_price;
        var keyword = HOMEY_map_vars.keyword;
        var search_lat = HOMEY_map_vars.lat;
        var search_lng = HOMEY_map_vars.lng;
        var radius = HOMEY_map_vars.radius;
        var bedrooms = HOMEY_map_vars.bedrooms;
        var rooms = HOMEY_map_vars.rooms;
        var room_size = HOMEY_map_vars.room_size;
        var area = HOMEY_map_vars.area;
        var amenity = HOMEY_map_vars.amenity;
        var facility = HOMEY_map_vars.facility;
        var not_found = HOMEY_map_vars.not_found;
        var infoboxClose = HOMEY_map_vars.infoboxClose;
        var bedrooms_icon = HOMEY_map_vars.bedrooms_icon;
        var bathroom_icon = HOMEY_map_vars.bathroom_icon;
        var guests_icon = HOMEY_map_vars.guests_icon;
        var securityhomeyMap = $('#securityhomeyMap').val();
        var paged = 0;
        var compare_url = HOMEY_ajax_vars.compare_url;
        var add_compare = HOMEY_ajax_vars.add_compare;
        var remove_compare = HOMEY_ajax_vars.remove_compare;
        var compare_limit = HOMEY_ajax_vars.compare_limit;

        var homey_is_mobile = false;
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            homey_is_mobile = true;
        }

        var homeyMapTileLayer = function() {
            if(is_mapbox == 'mapbox' && api_mapbox != '') {

                //var tileLayer = L.tileLayer( 'https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token='+api_mapbox, {
                var tileLayer = L.tileLayer( 'https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token='+api_mapbox, {
                    attribution: '© <a href="https://www.mapbox.com/about/maps/">Mapbox</a> © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> <strong><a href="https://www.mapbox.com/map-feedback/" target="_blank">Improve this map</a></strong>',
                    tileSize: 512,
                    maxZoom: 18,
                    zoomOffset: -1,
                    id: 'mapbox/streets-v11',
                        accessToken: 'your.mapbox.access.token'
                    }
                );

            } else {
                var tileLayer = L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution : '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                } );
            }
            return tileLayer;
        }



        /*--------------------------------------------------------------------------
         *   Compare for ajax
         * -------------------------------------------------------------------------*/
        var compare_for_ajax_map = function() {
            var listings_compare = homeyGetCookie('homey_compare_listings');
            var limit_item_compare = 4;
            add_to_compare(compare_url, add_compare, remove_compare, compare_limit, listings_compare, limit_item_compare );
            remove_from_compare(listings_compare, add_compare, remove_compare);
        }


        var reloadMarkers = function() {
            // Loop through markers and set map to null for each
            for (var i=0; i<markers.length; i++) {

                //markers[i].setMap(null);
                homeyMap.removeLayer(markers[i]);
            }
            // Reset the markers array
            markers = [];
            if (osm_markers_cluster) {
                homeyMap.removeLayer(osm_markers_cluster);
            }
        }

        var getMapBounds = function(mapDataProperties) {
            // get map bounds
            var mapBounds = [];
            for( var i = 0; i < mapDataProperties.length; i++ ) {
                if ( mapDataProperties[i].lat && mapDataProperties[i].long ) {
                    mapBounds.push( [ mapDataProperties[i].lat, mapDataProperties[i].long ] );
                }
            }

            return mapBounds;
        }

        var homey_map_zoomin = function(hMap) {
            $('#listing-mapzoomin').on('click', function() {
                var current= parseInt( hMap.getZoom(),10);
                console.log(current);
                current++;
                if(current > 20){
                    current = 20;
                }
                hMap.setZoom(current);
            });
        }

        var homey_map_zoomout = function(hMap) {
            $('#listing-mapzoomout').on('click', function() {
                var current= parseInt( hMap.getZoom(),10);
                console.log(current);
                current--;
                if(current < 0){
                    current = 0;
                }
                hMap.setZoom(current);
            });
        }

        var homey_map_next = function(hMap) {
            current_marker++;
            if ( current_marker > markers.length ){
                current_marker = 1;
            }
            while( markers[current_marker-1].visible===false ){
                current_marker++;
                if ( current_marker > markers.length ){
                    current_marker = 1;
                }
            }
            if( hMap.getZoom() < 15 ){
                hMap.setZoom(15);
            }

            hMap.setView(markers[current_marker - 1].getLatLng());
            if (! markers[current_marker - 1]._icon) {
                markers[current_marker - 1].__parent.spiderfy();
            }

            hMap.setZoom(20);

            if( (current_marker - 1)==0 || (current_marker - 1)==markers.length ){
                setTimeout(function(){  markers[current_marker - 1].fire('click');  }, 500);
            }else{
                markers[current_marker - 1].fire('click');
            }


        }

        var homey_map_prev = function(hMap) {
            current_marker--;
            if (current_marker < 1){
                current_marker = markers.length;
            }
            while( markers[current_marker-1].visible===false ){
                current_marker--;
                if ( current_marker > markers.length ){
                    current_marker = 1;
                }
            }
            if( hMap.getZoom() < 15 ){
                hMap.setZoom(15);
            }

            hMap.setView(markers[current_marker - 1].getLatLng());
            if (! markers[current_marker - 1]._icon) {
                markers[current_marker - 1].__parent.spiderfy();
            }

            hMap.setZoom(20);

            if( (current_marker - 1)==0 || (current_marker )==markers.length ){
                setTimeout(function(){  markers[current_marker - 1].fire('click');  }, 500);
            }else{
                markers[current_marker - 1].fire('click');
            }
        }

        $('#homey-gmap-next').on('click', function(){
            homey_map_next(homeyMap);
        });

        $('#homey-gmap-prev').on('click', function(){
            homey_map_prev(homeyMap);
        });

        /*--------------------------------------------------------------------
        * Add Marker
        *--------------------------------------------------------------------*/
        var homeyAddMarkers = function(map_properties, homeyMap) {
            var propertyMarker;

            var mBounds = getMapBounds(map_properties);

            if ( 1 <= mBounds.length ) {
                homeyMap.fitBounds( mBounds );
            }

            if(pin_cluster == 'yes') {
                osm_markers_cluster = new L.MarkerClusterGroup({
                    iconCreateFunction: function (cluster) {
                        var markers1 = cluster.getAllChildMarkers();
                        var html = '<div class="homey-osm-cluster">' + markers1.length + '</div>';
                        return L.divIcon({ html: html, className: 'mycluster', iconSize: L.point(47, 47) });
                    },
                    spiderfyOnMaxZoom: true, showCoverageOnHover: true, zoomToBoundsOnClick: true
                });
            }

            for( var i = 0; i < map_properties.length; i++ ) {

                if ( map_properties[i].lat && map_properties[i].long ) {

                    var mapData = map_properties[i];

                    var mapCenter = L.latLng( mapData.lat, mapData.long );

                    var markerOptions = {
                        riseOnHover: true
                    };


                    if ( mapData.title ) {
                        markerOptions.title = mapData.title;
                    }


                    if( markerPricePins == 'yes' ) {
                        var pricePin = '<div  id="infobox_popup_'+map_properties[i].id+'"  data-id="'+map_properties[i].id+'" class="gm-marker gm-marker-color-'+map_properties[i].term_id+'"><div class="gm-marker-price">'+map_properties[i].price+'</div></div>';

                        var myIcon = L.divIcon({
                            className:'someclass',
                            iconSize: new L.Point(0, 0),
                            html: pricePin
                        });

                        if(pin_cluster == 'yes') {
                            propertyMarker = new L.Marker(mapCenter, {icon: myIcon});
                        } else {
                            propertyMarker = L.marker( mapCenter,{icon: myIcon} ).addTo( homeyMap );
                        }

                    } else {
                        // Marker icon

                        var marker_pin = map_properties[i].icon;
                        var marker_pin_retina = map_properties[i].retinaIcon

                        if ( marker_pin ) {

                            var iconOptions = {
                                iconUrl: marker_pin,
                                iconSize: [44, 56],
                                iconAnchor: [20, 57],
                                popupAnchor: [1, -57]
                            };
                            if ( marker_pin_retina ) {
                                iconOptions.iconRetinaUrl = marker_pin_retina;
                            }
                            markerOptions.icon = L.icon( iconOptions );
                        }

                        if(pin_cluster == 'yes') {
                            propertyMarker = new L.Marker(mapCenter, markerOptions);
                        } else {
                            propertyMarker = L.marker( mapCenter, markerOptions ).addTo( homeyMap );
                        }
                    }

                    if(pin_cluster == 'yes') {
                        osm_markers_cluster.addLayer(propertyMarker);
                    }

                    var bedrooms = "";
                    var baths = "";
                    var guests = "";
                    var listing_type = '';
                    if(map_properties[i].bedrooms != '') {
                        bedrooms = '<li>'+bedrooms_icon+'<span class="total-beds">'+map_properties[i].bedrooms+'</span></li>';
                    }

                    if(map_properties[i].baths != '') {
                        baths = '<li>'+bathroom_icon+'<span class="total-baths">'+map_properties[i].baths+'</span></li>';
                    }

                    if(map_properties[i].guests != '') {
                        guests = '<li>'+guests_icon+'<span class="total-guests">'+map_properties[i].guests+'</span></li>';
                    }
                    if(map_properties[i].listing_type != '') {
                        listing_type = '<li class="item-type">'+map_properties[i].listing_type+'</li>';
                    }

                    var infoboxContent = '<div id="google-maps-info-window" class="homey-open-street-map">'+
                        '<div class="item-wrap item-grid-view">'+
                        '<div class="media property-item">'+
                        '<div class="media-left">'+
                        '<div class="item-media item-media-thumb">'+
                        '<a href="'+map_properties[i].url+'" class="hover-effect">'+map_properties[i].thumbnail+'</a>'+
                        '<div class="item-media-price">'+
                        '<span class="item-price">'+map_properties[i].price+'</span>'+
                        '</div>'+
                        '</div>'+
                        '</div>'+
                        '<div class="media-body item-body clearfix">'+
                        '<div class="item-title-head">'+
                        '<div class="title-head-left">'+
                        '<h2 class="title">'+
                        '<a href="'+map_properties[i].url+'">'+mapData.title+'</a></h2>'+
                        '<address class="item-address">'+map_properties[i].address+'</address>'+
                        '</div>'+
                        '</div>'+
                        '<ul class="item-amenities">'+
                        bedrooms+
                        baths+
                        guests+
                        listing_type+
                        '</ul>'+
                        '</div>'+
                        '</div>'+
                        '</div>'+
                        '</div>';

                    markers.push(propertyMarker);
                    propertyMarker.bindPopup( infoboxContent );


                } // end if lat lng

            } // end for loop

            if( pin_cluster == 'yes' ) {
                homeyMap.addLayer(osm_markers_cluster);
            }

        } //end homeyAddMarkers


        var homey_make_search_call = function(current_form, current_page, _lat, _long, element, markerTarget, showMarkerLabels, defaultZoom, optimizedMapLoading, isHalfMap) {
            var mapDiv = $('#homey-halfmap');
            arrive = current_form.find('input[name="arrive"]').val();
            depart = current_form.find('input[name="depart"]').val();
            guests = current_form.find('input[name="guest"]').val();
            keyword = current_form.find('input[name="keyword"]').val();
            pets = current_form.find('input[name="pets"]:checked').val();
            search_area = current_form.find('input[name="search_area"]').val();
            search_city = current_form.find('input[name="search_city"]').val();
            search_country = current_form.find('input[name="search_country"]').val();
            search_lat = current_form.find('input[name="lat"]').val();
            search_lng = current_form.find('input[name="lng"]').val();
            radius = current_form.find('input[name="radius"]').val();
            booking_type = current_form.find('input[name="booking_type"]').val();
            listing_type = current_form.find('select[name="listing_type"]').val();
            country = current_form.find('select[name="country"]').val();
            state = current_form.find('select[name="state"]').val();
            city = current_form.find('select[name="city"]').val();
            area = current_form.find('select[name="area"]').val();
            start_hour = current_form.find('select[name="start"]').val();
            end_hour = current_form.find('select[name="end"]').val();

            if(listing_type=="" || listing_type==undefined) {
                listing_type = mapDiv.data('type');
            }

            if(booking_type=="" || booking_type==undefined) {
                booking_type = mapDiv.data('booking_type');
            }


            amenity = current_form.find('.amenities-list input[type=checkbox]:checked').map(function(_, el) {
                return $(el).val();
            }).toArray();

            facility = current_form.find('.facilities-list input[type=checkbox]:checked').map(function(_, el) {
                return $(el).val();
            }).toArray();

            min_price = current_form.find('select[name="min-price"]').val();
            max_price = current_form.find('select[name="max-price"]').val();
            bedrooms = current_form.find('select[name="bedrooms"]').val();
            rooms = current_form.find('select[name="rooms"]').val();
            room_size = current_form.find('select[name="room_size"]').val();

            var sort_by = $('#sort_listings_halfmap').val();

            if( current_page != undefined ) {
                paged = current_page;
            }

            var maptype = '';

            ajaxData = {
                action: 'homey_half_map',
                'arrive': arrive,
                'depart': depart,
                'guest': guests,
                'keyword': keyword,
                'pets': pets,
                'bedrooms': bedrooms,
                'rooms': rooms,
                'room_size': room_size,
                'search_country': search_country,
                'search_city': search_city,
                'search_area': search_area,
                'listing_type': listing_type,
                'min-price': min_price,
                'max-price': max_price,
                'country': country,
                'search_lat': search_lat,
                'search_lng': search_lng,
                'radius': radius,
                'state': state,
                'city': city,
                'area': area,
                'booking_type': booking_type,
                'start_hour': start_hour,
                'end_hour': end_hour,
                'amenity': amenity,
                'facility': facility,
                'sort_by': sort_by,
                'layout': layout,
                'num_posts': num_posts,
                'paged': current_page,
                'security': securityhomeyMap,
            };
            homeyMainMap(_lat, _long, element, markerTarget, showMarkerLabels, defaultZoom, optimizedMapLoading, isHalfMap, ajaxData, maptype);
        }

        /*--------------------------------------------------------------------------------------
        * Sticky Map
        *-------------------------------------------------------------------------------------*/
        var homeyStickyMap = function(element, showMarkerLabels, defaultZoom, mapPaged) {

            if( document.getElementById(element) != null ) {
                if( !defaultZoom ){
                    defaultZoom = 14;
                }

                var _lat = '';
                var _long = '';
                var ajax_Action = 'homey_sticky_map';

                var mapCenter = L.latLng( _lat, _long );

                var mapDragging = true;
                var mapOptions = {
                    dragging: mapDragging,
                    center: mapCenter,
                    zoom: 10,
                    tap: true
                };

                var container = L.DomUtil.get(element); if(container != null){ container._leaflet_id = null; }
                homeyMap = L.map( element, mapOptions );

                homeyMap.scrollWheelZoom.disable();

                homeyMap.addLayer( homeyMapTileLayer() );

                var allMarkers;

                var ajaxData = {
                    action: ajax_Action,
                    'paged': mapPaged,
                    'security': securityhomeyMap
                };

                //Load map Data
                var loadMapData = function(ajaxurl, ajaxData) {

                    $.ajax({
                        url: ajaxurl,
                        dataType: "json",
                        method: "POST",
                        data: ajaxData,
                        success: function(data) {


                            if(data.getListings === true) {

                                reloadMarkers();
                                homeyAddMarkers( data.listings, homeyMap );

                                $('#homey-map-loading').hide();

                            } else {

                            }

                        },
                        error : function (e) {
                            console.log(e);
                        }
                    });

                } // End loadMapData
                loadMapData(ajaxurl, ajaxData);

                if( document.getElementById('listing-mapzoomin') ) {
                    homey_map_zoomin(homeyMap);
                }
                if( document.getElementById('listing-mapzoomout') ) {
                    homey_map_zoomout(homeyMap);
                }



            } else {
                console.log("No map element found");
            }

        } // End homeyStickyMap

        /*--------------------------------------------------------------------------------------
        * Header Map
        *-------------------------------------------------------------------------------------*/
        var homeyMainMap = function(_lat, _long, element, markerTarget, showMarkerLabels, defaultZoom, optimizedMapLoading, isHalfMap, halfMapAjaxData, maptype) {

            if( document.getElementById(element) != null ) {
                if( !defaultZoom ){
                    defaultZoom = 14;
                }

                if( !optimizedMapLoading ) {
                    var optimizedMapLoading = 0;
                }

                var map_cities = '';

                var ajax_Action;

                if(isHalfMap) {
                    ajax_Action = 'homey_half_map';

                } else {
                    ajax_Action = 'homey_header_map';

                    if(maptype == 'lat_long') {

                    } else if(maptype == 'city') {
                        map_cities = header_map_cities;
                    }
                }

                var mapCenter = L.latLng( _lat, _long );

                var mapDragging = true;
                var mapOptions = {
                    dragging: mapDragging,
                    center: mapCenter,
                    zoom: defaultZoom,
                    tap: true
                };

                var container = L.DomUtil.get(element); if(container != null){ container._leaflet_id = null; }
                homeyMap = L.map( element, mapOptions );

                homeyMap.scrollWheelZoom.disable();

                homeyMap.addLayer( homeyMapTileLayer() );

                var allMarkers;

                if(isHalfMap) {
                    var ajaxData = halfMapAjaxData;
                } else {
                    var ajaxData = {
                        action: ajax_Action,
                        'map_cities': map_cities,
                        'security': securityhomeyMap,
                    };
                }


                //Load map Data
                var loadMapData = function(ajaxurl, ajaxData) {
                    if(typeof ajaxData != "undefined"){
                        $.ajax({
                            url: ajaxurl,
                            dataType: "json",
                            method: "POST",
                            data: ajaxData,
                            beforeSend: function() {
                                $('#homey-map-loading').show();

                                if(isHalfMap) {
                                    halfmap_ajax_container.empty().append(''
                                        +'<div id="homey-map-loading">'
                                        +'<div class="mapPlaceholder">'
                                        +'<div class="loader-ripple spinner">'
                                        +'<div class="bounce1"></div>'
                                        +'<div class="bounce2"></div>'
                                        +'<div class="bounce3"></div>'
                                        +'</div>'
                                        +'</div>'
                                        +'</div>'
                                    );
                                }
                            },
                            success: function(data) {
                                if(data.getListings === true) {
                                    reloadMarkers();
                                    homeyAddMarkers( data.listings, homeyMap );

                                    if(isHalfMap) {
                                        halfmap_ajax_container.empty().html(data.listingHtml);
                                        total_results.empty().html(data.total_results);
                                        homey_infobox_trigger();
                                    }

                                    homey_init_add_favorite(ajaxurl, userID, is_singular_listing);
                                    homey_init_remove_favorite(ajaxurl, userID, is_singular_listing);
                                    compare_for_ajax_map();

                                    if(isHalfMap) {
                                        half_map_ajax_pagi();
                                        $(".half-map-left-wrap, .half-map-right-wrap").animate({ scrollTop: 0 }, "slow");
                                    }

                                    $('#homey-map-loading').hide();
                                    homeyMap.setZoom(defaultZoom);

                                } else {
                                    reloadMarkers();

                                    //homeyMap.setCenter(new google.maps.LatLng(default_lat, default_lng));
                                    $('#homey-halfmap').append('<div class="map-notfound">'+not_found+'</div>');
                                    halfmap_ajax_container.empty().html('<div class="map-notfound">'+not_found+'</div>');
                                    total_results.empty().html(data.total_results);
                                    $(".loader-ripple").hide();


                                }
                            },
                            error : function (e) {
                                console.log(e);
                            }
                        });
                    }
                } // End loadMapData

                loadMapData(ajaxurl, ajaxData);

                if( document.getElementById('listing-mapzoomin') ) {
                    homey_map_zoomin(homeyMap);
                }
                if( document.getElementById('listing-mapzoomout') ) {
                    homey_map_zoomout(homeyMap);
                }

                var intervalVar = setInterval(function(){
                    if(typeof $("#homey-gmap-prev") != "undefined"){
                        //loadMapData();
                    }
                    clearInterval(intervalVar);
                }, 1500);

            } else {
                console.log("No map element found");
            }
        } // End homeyMap


        /*--------------------------------------------------------------------------------------
        * Simple Map
        *-------------------------------------------------------------------------------------*/
        var homeySimpleMap = function (_lat, _long, element, markerDragable, showCircle, defaultZoom, marker_pin, marker_pin_retina) {
            if (!markerDragable){
                markerDragable = false;
            }

            if(!showCircle) {
                showCircle = false;
            }

            if(!defaultZoom) {
                defaultZoom = 15;
            }

            var markerOptions = {
                riseOnHover: true
            };

            var mapCenter;

            var homeySimpleMarker = function(homeyMap) {

                if ( marker_pin ) {

                    var iconOptions = {
                        iconUrl: marker_pin,
                        iconSize: [44, 56],
                        iconAnchor: [20, 57],
                        popupAnchor: [1, -57]
                    };
                    if ( marker_pin_retina ) {
                        iconOptions.iconRetinaUrl = marker_pin_retina;
                    }
                    markerOptions.icon = L.icon( iconOptions );
                }

                propertyMarker = L.marker( mapCenter, markerOptions ).addTo( homeyMap );
            }

            var homeyCircle = function(homeyMap) {

                var Circle = L.circle(mapCenter, 200).addTo(homeyMap);
            }

            mapCenter = L.latLng(_lat, _long);
            var drawMap = function(mapCenter){
                var mapOptions = {
                    dragging: markerDragable,
                    center: mapCenter,
                    zoom: defaultZoom,
                    tap: false
                };

                var mapElement = document.getElementById(element);
                homeyMap = L.map( mapElement, mapOptions );

                homeyMap.scrollWheelZoom.disable();
                homeyMap.addLayer( homeyMapTileLayer() );

                if(!showCircle) {
                    homeySimpleMarker(homeyMap);
                }

                if(showCircle) {
                    homeyCircle(homeyMap);
                }

            }
            drawMap(mapCenter);


        } // homeySimpleMap



        if($('#banner-map').length > 0) {
            var mapDiv = $('#banner-map');
            var zoomlevel = mapDiv.data('zoomlevel');
            var maptype = mapDiv.data('maptype');
            var maplat = mapDiv.data('lat');
            var maplong = mapDiv.data('long');
            var ajaxData = null;

            var _lat = '';//maplat;
            var _long = '';//maplong;
            var element = "banner-map";
            var markerTarget = "infobox";
            var showMarkerLabels = true;
            var isHalfMap = false;
            var defaultZoom = zoomlevel;
            var optimizedMapLoading = 0; // 0/1 If enable map will load data when map moved within it's bounds otherwise will load data at once
            homeyMainMap(_lat, _long, element, markerTarget, showMarkerLabels, defaultZoom, optimizedMapLoading, isHalfMap, ajaxData, maptype)
        }


        // Single listing map
        if($('#homey-single-map').length > 0 ) {
            var mapDiv = $('#homey-single-map');
            var zoomlevel = mapDiv.data('zoom');
            var pin_type = mapDiv.data('pin-type');
            var marker_pin = mapDiv.data('marker-pin');
            var marker_pin_retina = mapDiv.data('marker-pin-retina');
            var _lat   = mapDiv.data('lat');
            var _long  = mapDiv.data('long');
            var element     = 'homey-single-map';
            var defaultZoom = zoomlevel;
            var markerDragable = false;
            if(pin_type == 'marker') {
                var showCircle = false;
            } else {
                var showCircle = true;
            }
            homeySimpleMap(_lat, _long, element, markerDragable, showCircle, defaultZoom, marker_pin, marker_pin_retina);
        }

        // Single listing map
        if($('#homey_sticky_map').length > 0 ) {
            var mapDiv = $('#homey_sticky_map');
            var element     = 'homey_sticky_map';
            var defaultZoom = 12;
            var showMarkerLabels = true;
            var mapPaged = mapDiv.data('mappaged');
            homeyStickyMap(element, showMarkerLabels, defaultZoom, mapPaged);
        }

        if($('#homey-halfmap').length > 0) {
            var mapDiv = $('#homey-halfmap');
            var zoomlevel = mapDiv.data('zoom');

            var layout = mapDiv.data('layout');
            var num_posts = mapDiv.data('num-posts');
            var order = mapDiv.data('order');
            var type = mapDiv.data('type');
            var booking_type = mapDiv.data('booking_type');
            var maptype = '';

            if(listing_type=="") {
                listing_type = type;
            }

            var _lat = '';
            var _long = '';
            var element = "homey-halfmap";
            var markerTarget = "infobox";
            var showMarkerLabels = true;
            var isHalfMap = true;
            var defaultZoom = zoomlevel;
            var optimizedMapLoading = 0; // 0/1 If enable map will load data when map moved within it's bounds otherwise will load data at once

            var ajaxData = {
                action: 'homey_half_map',
                'arrive': arrive,
                'depart': depart,
                'guest': guests,
                'keyword': keyword,
                'pets': pets,
                'bedrooms': bedrooms,
                'rooms': rooms,
                'room_size': room_size,
                'search_country': search_country,
                'search_city': search_city,
                'search_area': search_area,
                'listing_type': listing_type,
                'min-price': min_price,
                'max-price': max_price,
                'country': country,
                'state': state,
                'city': city,
                'area': area,
                'booking_type': booking_type,
                'search_lat': search_lat,
                'search_lng': search_lng,
                'radius': radius,
                'start_hour': start_hour,
                'end_hour': end_hour,
                'amenity': amenity,
                'facility': facility,
                'layout': layout,
                'num_posts': num_posts,
                'sort_by': order,
                'paged': paged,
                'security': securityhomeyMap,
            };


            homeyMainMap(_lat, _long, element, markerTarget, showMarkerLabels, defaultZoom, optimizedMapLoading, isHalfMap, ajaxData, maptype);

            $('.homey_half_map_search_btn').on('click', function(e) {
                e.preventDefault();
                var current_form = $(this).parents('.half-map-wrap');
                var current_page = 1;
                homey_make_search_call(current_form, current_page, _lat, _long, element, markerTarget, showMarkerLabels, defaultZoom, optimizedMapLoading, isHalfMap);

            });

            $('#sort_listings_halfmap').on('change', function(e) {
                e.preventDefault();
                var current_form = $(this).parents('.half-map-wrap');
                var current_page = 1;
                homey_make_search_call(current_form, current_page, _lat, _long, element, markerTarget, showMarkerLabels, defaultZoom, optimizedMapLoading, isHalfMap);

            });

            var half_map_ajax_pagi = function() {
                $('.half_map_ajax_pagi a').on('click', function(e){
                    e.preventDefault();
                    var current_page = $(this).data('homeypagi');
                    var current_form = $(this).parents('.half-map-wrap');
                    homey_make_search_call(current_form, current_page, _lat, _long, element, markerTarget, showMarkerLabels, defaultZoom, optimizedMapLoading, isHalfMap);
                });
                return false;
            } // enf half_map_ajax_pagi

            var radius_search_slider = function(default_radius) {
                $("#radius-range-slider").slider(
                    {
                        value: default_radius,
                        min: 0,
                        max: 100,
                        step: 1,
                        slide: function (event, ui) {
                            $("#radius-range-text").html(ui.value);
                            $("#radius-range-value").val(ui.value);
                        },
                        stop: function( event, ui ) {

                            var current_form = $(this).parents('.half-map-wrap');
                            var current_page = 1;
                            homey_make_search_call(current_form, current_page, _lat, _long, element, markerTarget, showMarkerLabels, defaultZoom, optimizedMapLoading, isHalfMap);
                        }
                    }
                );

                $("#radius-range-text").html($('#radius-range-slider').slider('value'));
                $("#radius-range-value").val($('#radius-range-slider').slider('value'));
            }

            if($( "#radius-range-slider").length > 0 ) {

                radius_search_slider(homey_default_radius);

            }

        }

        /*-----------------------------------------------------------------------------------------
        * Auto Complete
        *-----------------------------------------------------------------------------------------*/
        if( $("input.input-search").length > 0 ) {
            jQuery('input.input-search').autocomplete( {
                source: function ( request, response ) {
                    jQuery.get( 'https://nominatim.openstreetmap.org/search', {
                        format: 'json',
                        q: request.term,//was q
                        addressdetails:'1',
                    }, function( result ) {

                        if ( !result.length ) {
                            response( [ {
                                value: '',
                                label: 'there are no results'
                            } ] );
                            return;
                        }
                        response( result.map( function ( item ) {
                            var return_obj= {
                                label: item.display_name,
                                latitude: item.lat,
                                longitude: item.lon,
                                value: item.display_name,
                            };


                            if(typeof(item.address) != 'undefined') {
                                return_obj.county = item.address.county;
                            }

                            if(typeof(item.address) != 'undefined') {
                                return_obj.city = item.address.city;
                            }

                            if(typeof(item.address) != 'undefined') {
                                return_obj.state=item.address.state;
                            }

                            if(typeof(item.address) != 'undefined') {
                                return_obj.country=item.address.country;
                            }

                            if(typeof(item.address) != 'undefined') {
                                return_obj.zip=item.address.postcode;
                            }

                            if(typeof(item.address) != 'undefined') {
                                return_obj.country_short=item.address.country_code;
                            }

                            return return_obj
                        } ) );
                        jQuery("#ui-id-2").show();
                    }, 'json' );
                },
                select: function ( event, ui ) {

                    $('input[name="lat"]').val(ui.item.latitude);
                    $('input[name="lng"]').val(ui.item.longitude);

                    $('input[name="search_area"]').attr('data-value', '');
                    $('input[name="search_area"]').val('');

                    $('input[name="search_city"]').attr('data-value', ui.item.city);
                    $('input[name="search_city"]').val(ui.item.city);

                    $('input[name="search_country"]').attr('data-value', ui.item.country);
                    $('input[name="search_country"]').val(ui.item.country);
                    jQuery("#ui-id-2").hide();
                }
            });
        } // Auto complete

        /*--------------------------------------------------------------------------
        * Add/Edit listing for autocomplete
        *---------------------------------------------------------------------------*/
        var homey_osm_marker_position = function(lat, long) {
            var mapCenter       = L.latLng( lat, long );
            var markerCenter    = L.latLng(mapCenter);
            homeyMap.removeLayer( mapMarker );

            // Marker
            var osmMarkerOptions = {
                riseOnHover: true,
                draggable: true
            };
            mapMarker = L.marker( mapCenter, osmMarkerOptions ).addTo( homeyMap );
        }

        var homey_init_submit_map = function() {

            if( jQuery('#map').length === 0 ) {
                return;
            }


            var mapDiv = $('#map');
            var maplat = mapDiv.data('add-lat');
            var maplong = mapDiv.data('add-long');

            if(maplat ==='' || typeof  maplat === 'undefined') {
                maplat = 25.686540;
            }

            if(maplong ==='' || typeof  maplong === 'undefined') {
                maplong = -80.431345;
            }

            maplat = parseFloat(maplat);
            maplong = parseFloat(maplong);

            var mapCenter = L.latLng( maplat, maplong );
            homeyMap =  L.map( 'map',{
                center: mapCenter,
                zoom: 15,
            });

            homeyMap.scrollWheelZoom.disable();

            var tileLayer =  homeyMapTileLayer();
            homeyMap.addLayer( tileLayer );

            // Marker
            var osmMarkerOptions = {
                riseOnHover: true,
                draggable: true
            };
            mapMarker = L.marker( mapCenter, osmMarkerOptions ).addTo( homeyMap );

            mapMarker.on('drag', function(e){
                document.getElementById('lat').value = mapMarker.getLatLng().lat;
                document.getElementById('lng').value = mapMarker.getLatLng().lng;
            });

            homeyMap.invalidateSize();
        } // End homey_init_submit_map
        homey_init_submit_map();

        var homey_osm_marker_position = function(lat, long) {
            var latLng = L.latLng( lat, long );
            mapMarker.setLatLng( latLng );

            homeyMap.invalidateSize();
            homeyMap.panTo(new L.LatLng(lat,long));

            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = long;
        }

        var homey_submit_autocomplete = function() {

            jQuery('#listing_address').autocomplete( {
                source: function ( request, response ) {
                    jQuery.get( 'https://nominatim.openstreetmap.org/search', {
                        format: 'json',
                        q: request.term,
                        addressdetails:'1',
                    }, function( result ) {
                        if ( !result.length ) {
                            response( [ {
                                value: '',
                                label: 'there are no results'
                            } ] );
                            return;
                        }
                        response( result.map( function ( item ) {
                            var return_obj= {
                                label: item.display_name,
                                latitude: item.lat,
                                longitude: item.lon,
                                value: item.display_name,
                            };


                            if(typeof(item.address) != 'undefined') {
                                return_obj.county = item.address.county;
                            }

                            if(typeof(item.address) != 'undefined') {
                                return_obj.city = item.address.city;
                            }

                            if(typeof(item.address) != 'undefined') {
                                return_obj.state=item.address.state;
                            }

                            if(typeof(item.address) != 'undefined') {
                                return_obj.country=item.address.country;
                            }

                            if(typeof(item.address) != 'undefined') {
                                return_obj.zip=item.address.postcode;
                            }

                            if(typeof(item.address) != 'undefined') {
                                return_obj.country_short=item.address.country_code;
                            }

                            return return_obj
                        }));
                    }, 'json' );
                },
                select: function ( event, ui ) {

                    var property_lat     =   ui.item.latitude;
                    var property_long    =   ui.item.longitude;

                    $('#zip').val( ui.item.zip );
                    $('#countyState').val( ui.item.county);
                    $('#city').val( ui.item.city);
                    $('#homey_country').val( ui.item.country);
                    $('input[name="country_short"]').val( ui.item.country_short);
                    homey_osm_marker_position(property_lat, property_long);
                    $('#city, #countyState, #area, #homey_country').selectpicker('refresh');
                }
            } );

        } // end homey_submit_autocomplete
        homey_submit_autocomplete();

        var homey_infobox_trigger = function() {
            $('.infobox_trigger').each(function(i) {
                $(this).on('mouseenter', function() {
                    markers[i].fire('click');
                });

                $(this).on('mouseleave', function() {
                    //homeyMap.removeLayer(markers[i]);
                });
            });
            return false;
        }

        var homey_find_address_osm = function() {
            $('#find').on('click', function(e) {
                e.preventDefault();
                var address = $('input[name="listing_address"]').val().replace( /\n/g, ',' ).replace( /,,/g, ',' );

                if(!address) {
                    return;
                }

                $.get( 'https://nominatim.openstreetmap.org/search', {
                    format: 'json',
                    q: address,
                    limit: 1,
                }, function( result ) {
                    if ( result.length !== 1 ) {
                        return;
                    }
                    homey_osm_marker_position(result[0].lat, result[0].lon);

                }, 'json' );

            })
        }

        homey_find_address_osm();

        $(".homey_find_address_osm").click(function (){
            var address = $('input[name="listing_address"]').val().replace( /\n/g, ',' ).replace( /,,/g, ',' );

            if(!address) {
                return;
            }

            $.get( 'https://nominatim.openstreetmap.org/search', {
                format: 'json',
                q: address,
                limit: 1,
            }, function( result ) {
                if ( result.length !== 1 ) {
                    return;
                }
                homey_osm_marker_position(result[0].lat, result[0].lon);

            }, 'json' );
        });

    }// typeof HOMEY_map_vars

})(jQuery); // end function