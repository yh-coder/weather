require("./bootstrap");

$('[data-toggle="popover"]').popover();

$(window).on("load", function() {
    resizeMaps();
    $("body")
        .find(".getmaps")
        .eq(0)
        .click();
});
$(window).resize(function() {
    resizeMaps();
});

function resizeMaps() {
    if ($("#weather-list").length > 0) {
        let w = $("#weather-list").width();
        $("#maps")
            .closest("div")
            .css({ width: `${w}px` });
    }
}

// google maps 表示
$("body").on("click", ".getmaps", function() {
    let e = $(this).closest(".city-info");
    $("#map-name").html(`${e.data("name")} 周辺地図`);
    $("#maps").attr(
        "src",
        `https://maps.google.com/maps?output=embed&z=15&q=${e.data("lat")},${e.data("lon")}`
    );
});

// 地球儀表示
async function showEarth(canvas, region_id, country_id) {
    // サイズを指定
    const width = 640;
    const height = 640;
    // レンダラーを作成
    const renderer = new THREE.WebGLRenderer({ canvas: canvas });
    renderer.setPixelRatio(window.devicePixelRatio);
    renderer.setSize(width, height);
    renderer.setClearColor(0xf8fafc, 1.0);
    // シーンを作成
    const scene = new THREE.Scene();
    scene.fog = new THREE.Fog(0xf8fafc, 50, 250);
    // カメラを作成
    const camera = new THREE.PerspectiveCamera(45, width / height);

    let dotGeometry = new THREE.Geometry();
    dotGeometry.vertices.push(new THREE.Vector3(0, 0, 0));
    let normal = new THREE.PointsMaterial({ size: 2, sizeAttenuation: false, color: 0xcccccc });
    let emphasis = new THREE.PointsMaterial({ size: 3, sizeAttenuation: false, color: 0x00cc00 });
    normal.fog = true;

    let camera_type = 1;
    let url = "/api/earth";
    if (typeof country_id === "number") {
        url += "?country_id=" + country_id;
    } else if (typeof region_id === "number") {
        url += "?region_id=" + region_id;
    } else {
        camera_type = 0;
    }
    // 座標取得API
    let res = await axios.get(url);
    // 座標プロット
    let range = getPoints(
        res.data,
        scene,
        normal,
        camera_type == 0 ? normal : emphasis,
        dotGeometry
    );
    
    function getPoints(data, scene, normal, emphasis, dotGeometry) {
        // 強調範囲
        let range = {
            lon: [180, -180],
            lat: [90, -90]
        };

        data.coordinate.forEach(v => {
            const sprite = new THREE.Points(dotGeometry, v[2] == 0 ? normal : emphasis);
            if (v[2] > 0) {
                if (v[0] < range.lon[0]) {
                    range.lon[0] = v[0];
                }
                if (v[0] > range.lon[1]) {
                    range.lon[1] = v[0];
                }
                if (v[1] < range.lat[0]) {
                    range.lat[0] = v[1];
                }
                if (v[1] > range.lat[1]) {
                    range.lat[1] = v[1];
                }
            }
            sprite.position.x =
                50 * Math.sin((Math.PI * v[0]) / 180) * Math.cos((Math.PI * v[1]) / 180);
            sprite.position.z =
                50 * Math.cos((Math.PI * v[0]) / 180) * Math.cos((Math.PI * v[1]) / 180);
            sprite.position.y = 50 * Math.sin((Math.PI * v[1]) / 180);

            // 必要に応じてスケールを調整
            sprite.scale.set(1, 1, 1);
            scene.add(sprite);
        });
        return range;
    }

    function ticktack() {
        let longitude = Date.now() / 8000;
        let latitude = Math.sin(Date.now() / 12345) / 2;
        if (camera_type == 1) {
            let lon_mid = (range.lon[0] + range.lon[1]) / 2;
            let lat_mid = (range.lat[0] + range.lat[1]) / 2;
            let lon_size = (range.lon[1] - range.lon[0]) / 2;
            let lat_size = (range.lat[1] - range.lat[0]) / 2;
            longitude = ((lon_mid + lon_size * Math.sin(Date.now() / 4000)) * Math.PI) / 180;
            latitude = ((lat_mid + lat_size * Math.cos(Date.now() / 4000)) * Math.PI) / 180;
        }
        camera.position.x = 150 * Math.sin(longitude) * Math.cos(latitude);
        camera.position.z = 150 * Math.cos(longitude) * Math.cos(latitude);
        camera.position.y = 150 * Math.sin(latitude);

        camera.lookAt(new THREE.Vector3(0, 0, 0));
        // レンダリング
        renderer.render(scene, camera);
        requestAnimationFrame(ticktack);
    }
    
    ticktack();
    
    $('#earth-wrap').removeClass('d-none');
}


// 初期化
if ($("#earth").length == 1) {
    let param = location.pathname.split("/");
    let arg = [];
    param.forEach(v => {
        arg.push(v.match(/^\d+$/) ? Number(v) : null);
    });
    showEarth($("#earth").get(0), arg[1], arg[2]);
}
