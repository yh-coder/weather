// 座標データ
let points;
// Scene
let scene;
let target = [];

// 地域強調
$("body").on("mouseenter", "a.region", function() {
    let region_id = $(this).data("region-id");
    target = points.filter(point => point[1].find(v => v == region_id) == region_id);
    target.forEach(v => {
        let p = scene.getObjectByName(v[0][0] * 1000 + v[0][1]);
        p.material.color.setHex(0x90c490);
        p.material.size = 3;
    });
});
// 国強調
$("body").on("mouseenter", "a.country", function() {
    let country_id = $(this).data("country-id");
    target = points.filter(point => point[2].find(v => v == country_id) == country_id);
    target.forEach(v => {
        let p = scene.getObjectByName(v[0][0] * 1000 + v[0][1]);
        p.material.color.setHex(0x90c490);
        p.material.size = 3;
    });
});
// 強調解除
$("body").on("mouseleave", "a", function() {
    target.forEach(v => {
        let p = scene.getObjectByName(v[0][0] * 1000 + v[0][1]);
        p.material.color.setStyle("rgb(212, 214, 216)");
        p.material.size = 2;
    });
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
    scene = new THREE.Scene();
    scene.fog = new THREE.Fog(0xf8fafc, 100, 225);
    // カメラを作成
    const camera = new THREE.PerspectiveCamera(45, width / height);

    let dotGeometry = new THREE.Geometry();
    dotGeometry.vertices.push(new THREE.Vector3(0, 0, 0));

    // 座標取得API
    let res = await axios.get("/js/all.json");
    points = res.data.coordinate;
    // 座標プロット
    plotPoints(points, scene, dotGeometry);
    // 範囲取得
    let cameraRange = getCameraRange(points, region_id, country_id);

    function plotPoints(data, scene, dotGeometry) {
        data.forEach(v => {
            let material = new THREE.PointsMaterial({
                size: 2,
                sizeAttenuation: false,
                color: "rgb(212, 214, 216)"
            });
            material.fog = true;
            const dot = new THREE.Points(dotGeometry, material);
            dot.position.x =
                50 * Math.sin((Math.PI * v[0][0]) / 180) * Math.cos((Math.PI * v[0][1]) / 180);
            dot.position.z =
                50 * Math.cos((Math.PI * v[0][0]) / 180) * Math.cos((Math.PI * v[0][1]) / 180);
            dot.position.y = 50 * Math.sin((Math.PI * v[0][1]) / 180);
            dot.name = v[0][0] * 1000 + v[0][1];
            scene.add(dot);
        });
    }

    function getCameraRange(data, region_id, country_id) {
        // 範囲
        let camera = {
            type: 0,
            lon: [180, -180],
            lat: [90, -90]
        };
        let lont = [360, 0];
        let target = [];
        if (typeof country_id === "number") {
            target = data.filter(point => point[2].find(v => v == country_id) == country_id);
            camera.type = 1;
        } else if (typeof region_id === "number") {
            target = data.filter(point => point[1].find(v => v == region_id) == region_id);
            camera.type = 1;
        }
        target.forEach(v => {
            let t = v[0][0] < 0 ? 360 + v[0][0] : v[0][0];
            if (t < lont[0]) {
                lont[0] = t;
            }
            if (t > lont[1]) {
                lont[1] = t;
            }
            if (v[0][0] < camera.lon[0]) {
                camera.lon[0] = v[0][0];
            }
            if (v[0][0] > camera.lon[1]) {
                camera.lon[1] = v[0][0];
            }
            if (v[0][1] < camera.lat[0]) {
                camera.lat[0] = v[0][1];
            }
            if (v[0][1] > camera.lat[1]) {
                camera.lat[1] = v[0][1];
            }
        });
        if (camera.lon[1] - camera.lon[0] > 180) {
            camera.lon[0] = lont[0];
            camera.lon[1] = lont[1];
        }
        
        camera.lon_mid = (camera.lon[0] + camera.lon[1]) / 2;
        camera.lat_mid = (camera.lat[0] + camera.lat[1]) / 2;
        camera.lon_size = (camera.lon[1] - camera.lon[0]) / 2;
        camera.lat_size = (camera.lat[1] - camera.lat[0]) / 2;

        return camera;
    }

    function ticktack() {
        let longitude = Date.now() / 8000;
        let latitude = Math.sin(Date.now() / 12345) / 2;
        if (cameraRange.type == 1) {
            longitude = ((cameraRange.lon_mid + cameraRange.lon_size * 0.8 * Math.sin(Date.now() / 4000)) * Math.PI) / 180;
            latitude = ((cameraRange.lat_mid + cameraRange.lat_size *  0.8 * Math.cos(Date.now() / 4000)) * Math.PI) / 180;
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

    $("#earth-wrap").removeClass("d-none");
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
