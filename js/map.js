function TMap(map) {
    this._map = map;
    this._mvcArray = new google.maps.MVCArray([]);
    this._bounds = new google.maps.LatLngBounds();
}

Object.defineProperties(TMap.prototype, {
    map: {
        get: function () { return this._map; },
        set: function (map) { this._map = map; }
    },
    update: {
        value: function (ll) {
            this.bounds.extend(ll);
            this.map.fitBounds(this.bounds);
        }
    },
    convert: {
        value: function (w) {
            return (w * 6) + 1;
        }
    },
    bounds: {
        get: function () { return this._bounds; }
    },
    mvcArray: {
        get: function () { return this._mvcArray; }
    }
});

function HeatMap(map) {
    var $this = this;
    TMap.call($this, map);

    (new google.maps.visualization.HeatmapLayer({
        data: $this.mvcArray
    })).setMap($this.map);

    Object.defineProperties(this, {
        add: {
            value: function (lat, lng) {
                var ll = new google.maps.LatLng(lat, lng);
                $this.mvcArray.push(ll);
                $this.update(ll);
            }
        }
    })
}
HeatMap.prototype = Object.create(TMap.prototype);

function PosMap(map) {
    var $this = this;
    TMap.call($this, map);

    (new google.maps.visualization.HeatmapLayer({
        data: $this.mvcArray,
        gradient: ['rgba(0,0,0,0)', '#6699FF', '#5DA2E8', '#53ACD1', '#4AB5B9', '#41BEA2',
            '#38C78B', '#2ED174', '#25DA5D', '#1CE346', '#13EC2E', '#09F617', '#00FF00']
    })).setMap($this.map);

    Object.defineProperties(this, {
        add: {
            value: function (lat, lng, w) {
                var ll = new google.maps.LatLng(lat, lng);
                $this.mvcArray.push({ location: ll, weight: $this.convert(w) });
                $this.update(ll);
            }
        }
    })
}
PosMap.prototype = Object.create(TMap.prototype);

function NegMap(map) {
    var $this = this;
    TMap.call($this, map);

    (new google.maps.visualization.HeatmapLayer({
        data: $this.mvcArray,
        gradient: ['rgba(0,0,0,0)', '#6699FF', '#748BE8', '#827DD1', '#906FB9', '#9E61A2',
            '#AC538B', '#B94674', '#C7385D', '#D52A46', '#E31C2E', '#F10E17', '#FF0000']
    })).setMap($this.map);

    Object.defineProperties(this, {
        add: {
            value: function (lat, lng, w) {
                if (w < 0) w *= -1;
                var ll = new google.maps.LatLng(lat, lng);
                $this.mvcArray.push({ location: ll, weight: $this.convert(w) });
                $this.update(ll);
            }
        }
    })
}
NegMap.prototype = Object.create(TMap.prototype);

function TextMap(map) {
    var $this = this;
    TMap.call($this, map);

    var infoWindow = null;

    Object.defineProperties(this, {
        add: {
            value: function (lat, lng, text) {
                var ll = new google.maps.LatLng(lat, lng);
                var marker = new google.maps.Marker({
                    position: ll,
                    map: $this.map
                });
                $this.addInfo(marker, text);
                $this.update(ll);
            }
        },
        addInfo: {
            value: function (marker, text) {
                var info = ($("<div class='info_content' />").html($("<p />").html(text)))[0];
                google.maps.event.addListener(marker, 'click', (function (marker, info) {
                    if (infoWindow) infoWindow.close();
                    infoWindow = new google.maps.InfoWindow();
                    return function () {
                        infoWindow.setContent(info);
                        infoWindow.open($this.map, marker);
                    }
                })(marker, info));
            }
        }
    })
}
TextMap.prototype = Object.create(TMap.prototype);

TweetMaps = {};