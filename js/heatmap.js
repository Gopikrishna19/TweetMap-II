
function heatMap() {
    (new google.maps.visualization.HeatmapLayer({
        data: mvcArray,
        gradient: ['rgba(0,0,0,0)', '#FF0000', '#D5002B', '#AA0055', '#800080', '#5500AA', '#2B00D5',
                   '#0000FF', '#002BD5', '#0055AA', '#008080', '#00AA55', '#00D52B', '#00FF00']
    })).setMap(map);
}

function updateMap(lat, lng, w) {
    var ll = new google.maps.LatLng(lat, lng);
    mvcArray.push({ location: ll, weight: convert(w) });
    bounds.extend(ll);
    map.fitBounds(bounds);
}

function convert(w) {
    return ((w + 1) * 7) + 1;
}