/// <reference path='map.js' />

google.maps.event.addDomListener(window, 'load', initialize);

function initialize() {
    var def = new google.maps.LatLng(51.508742, -0.120850);
    var mapProp = {
        center: def,
        zoom: 2,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    TweetMaps.pmap = new PosMap(new google.maps.Map(document.getElementById("posMap"), mapProp));
    TweetMaps.nmap = new NegMap(new google.maps.Map(document.getElementById("negMap"), mapProp));
    TweetMaps.hmap = new HeatMap(new google.maps.Map(document.getElementById("heatMap"), mapProp));
    TweetMaps.tmap = new TextMap(new google.maps.Map(document.getElementById("textMap"), mapProp));
}

function updateMaps(lat, lng, w, text) {
    if (w < 0) {
        TweetMaps.nmap.add(lat, lng, w);
    } else if (w > 0) {
        TweetMaps.pmap.add(lat, lng, w);
    } else {
        TweetMaps.pmap.add(lat, lng, w);
        TweetMaps.nmap.add(lat, lng, w);
    }
    TweetMaps.hmap.add(lat, lng);
    TweetMaps.tmap.add(lat, lng, text);

    updateScale(w);
}

var point, weight = 0, left = 50;

function updateScale(w) {
    weight += w;
    left = (weight + 1) * 25;
    console.log(left);
    if (left < 0) left = 0;
    else if (left > 100) left = 100;
    point.css({ 'left': left + '%' });
    $(".info .sent").html(weight.toFixed(2));
}

$(function () {
    $(".max").click(function () {
        var box = $(this).closest(".box");
        if (box.hasClass("full")) {
            box.removeClass("full");
            $(".maps").removeClass("single");
        }
        else {
            box.addClass("full");
            $(".maps").addClass("single");
        }
        setTimeout(function () {
            for (tmap in TweetMaps) {
                map = TweetMaps[tmap].map;
                var center = map.getCenter();
                google.maps.event.trigger(map, "resize");
                map.setCenter(center);
            }
        }, 300);
    });

    point = $(".info .scale .point");

    $.ajax({
        url: "Server/start.php",
        timeout: 1000,
        complete: function () {
            console.log("Started socket server");
            setTimeout(function () {
                openSocket();
            }, 2000);
        }
    });

    $(".stop").click(function () {
        $.ajax({ url: "Server/stop.php" });
    });
})