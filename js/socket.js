function openSocket() {
    console.log("Open Socket");
    var ws = new WebSocket("ws://" + window.location.hostname + ":7765");
    ws.onopen = function (e) {
        console.log("Connected to server");
    }
    ws.onmessage = function (e) {
        var data = JSON.parse(e.data.toString());
        var lat = data.lat, lng = data.lng;
        switch (data.code) {
            case 1010: // close connection
            case 1001: // open connection
                console.log(data.code, data.msg);
                break; ;
            case 1004: // new status
                console.log(data.code, "New Tweet");
                updateMaps(parseFloat(data.lat), parseFloat(data.lng), parseFloat(data.score), data.text);
                break;
            case 1007: // something went wrong
            case 1008: // alchemy parse error
                console.log(data.code, "Tweet skipped");
                break;
            default: console.log(data);
        }
    }
    window.onbeforeunload = function () {
        ws.onclose = function () { };
        ws.close()
    };
}