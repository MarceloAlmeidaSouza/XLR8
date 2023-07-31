<!DOCTYPE html>
<html>

<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
    <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
    <style>
        .text-center {
            text-align: center;
        }
        #map {
            height:700px;
            flex: 1;
            width: 50%;
        }
        ._container{
            display:flex;
            column-gap: 5px;
        }
        ._container-table{
            flex: 1;
            width: 50%;
        }
    </style>
    <link rel='stylesheet' href='https://unpkg.com/leaflet@1.8.0/dist/leaflet.css' crossorigin='' />
</head>

<body>
    <h3 class='text-center'>Choose on the map the place of your next vacation</h3>
    <div class="_container">
        <div id='map'></div>
        <div class="_container-table">
            <table id="table" class="table table-bordered data-table" >
                <thead>
                    <tr>
                        <th>Hotel</th>
                        <th>Distance</th>
                        <th width="280px">Price</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <script src='https://unpkg.com/leaflet@1.8.0/dist/leaflet.js' crossorigin=''></script>
    <script>
        let table, map, chosenMarker={lat: 40.98238623678506, lng: -73.88742011880247}, markers = [];
        /* ----------------------------- Initialize Map ----------------------------- */
        function initMap() {
            map = L.map('map', {
                center: chosenMarker,
                zoom: 7
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            map.on('click', mapClicked);
        }
        initMap();

        /* --------------------------- Initialize Markers --------------------------- */
        function initMarkers(listMarkers=[]) {
            markers.forEach(m=>map.removeLayer(m));
            for (let index = 0; index < listMarkers.length; index++) {

                const data = listMarkers[index];
                const marker = generateMarker({lat:data.latitude, lng:data.longitude}, index);
                marker.addTo(map).bindPopup(`<b>${data.name}</b>`);
                map.panTo({lat:data.latitude, lng:data.longitude});
                markers.push(marker)
            }
        }

        function generateMarker(data, index) {
            return L.marker(data, {
                    draggable: false
                })
                .on('click', (event) => markerClicked(event, index))
                .on('dragend', (event) => markerDragEnd(event, index));
        }

        function centerMarker(latLng){
            var myCustomColour = 'red';

            var caption = '', // '<i class="fa fa-eye" />' or 'abc' or ...
                size = 10,    // size of the marker
                border = 2;   // border thickness

            var markerHtmlStyles = ' \
                background-color: ' + myCustomColour + '; \
                width: '+ (size * 3) +'px; \
                height: '+ (size * 3) +'px; \
                display: block; \
                left: '+ (size * -1.5) +'px; \
                top: '+ (size * -1.5) +'px; \
                position: relative; \
                border-radius: '+ (size * 3) +'px '+ (size * 3) +'px 0; \
                transform: rotate(45deg); \
                border: '+border+'px solid #FFFFFF;\
                ';

            var captionStyles = '\
                transform: rotate(-45deg); \
                display:block; \
                width: '+ (size * 3) +'px; \
                text-align: center; \
                line-height: '+ (size * 3) +'px; \
                ';

            var icon2 = L.divIcon({
                className: "color-pin-" + myCustomColour.replace('#', ''),

                // on another project this is needed: [0, size*2 + border/2]
                iconAnchor: [border, size*2 + border*2],

                labelAnchor: [-(size/2), 0],
                popupAnchor: [0, -(size*3 + border)],
                html: '<span style="' + markerHtmlStyles + '"><span style="'+captionStyles+'">'+ caption + '</span></span>'
            });

            var marker = L.marker(latLng, {icon: icon2})
            .addTo(map).addTo(map).bindPopup('Your chosen area');
            markers.push(marker);
            markers.push(L.circle(latLng, {radius: 150 * 1000}).addTo(map));
            map.panTo(new L.LatLng(latLng.lat, latLng.lng));
        }

        /* ------------------------- Handle Map Click Event ------------------------- */
        function mapClicked($event) {
            chosenMarker = {lat:$event.latlng.lat, lng:$event.latlng.lng};
            table.ajax.reload();
        }

        /* ------------------------ Handle Marker Click Event ----------------------- */
        function markerClicked($event, index) {
            console.log(map);
            console.log($event.latlng.lat, $event.latlng.lng);
        }

        /* ----------------------- Handle Marker DragEnd Event ---------------------- */
        function markerDragEnd($event, index) {
            console.log(map);
            console.log($event.target.getLatLng());
        }


  $(function () {

      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('{{csrf_token()}}').attr('content')
          }
    });

    table = $("#table").DataTable({
        ajax:{
            url: "{{ route('seach-by-coord') }}", //
            data: function(parm){
                return {
                    order_by:parm.columns[parm.order[0].column].data,
                    dir:parm.order[0].dir,
                    _token:'{{csrf_token()}}',
                    latitude: chosenMarker.lat,
                    longitude: chosenMarker.lng,
                    start:parm.start,
                    length:parm.length
                }
            },
            method:"POST",
            dataFilter: function(response){
                var json = jQuery.parseJSON( response);
                json.data = json.data.map(item=>{
                    item.distance = item.distance?.toFixed(3) + " km";
                    item.price = "$ " + item.price.replace(".",",");
                    return item;
                });
                json.recordsTotal = json.total;
                json.recordsFiltered = json.total;
                initMarkers(json.data);
                centerMarker(chosenMarker);

                return JSON.stringify(json); // return JSON string
            }
        },
        columnDefs: [{
                targets: 0,
                data: "name"
            },{
                targets: 1,
                data: "distance"
            },{
                targets: 2,
                data: "price"
            }
        ],
        autoFill: true,
        keys: true,
        lengthMenu: [[5, 10, 20, 50], ['5', '10', '20', "50"]],
        pageLength: 10,
        pagination: true,
        searching: false,
        ordering: true,
        serverSide: true,
        processing: true,
        order: [[ 0, "asc" ], [ 1, "asc" ], [2, "asc"]],
        dom: 'Blfrtip'
    });

  });
  </script>
</body>

</html>
