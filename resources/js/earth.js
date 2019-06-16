// カメラ
let camera;
// レンダラー
let renderer;
// シーン
let scene;
// 座標
let points;
// 強調座標
let target;
let default_target = [];

const bg_color = 0xf8fafc;
const default_dot_style = "rgb(212, 214, 216)";
const notice_dot_style = "rgb(144, 196, 144)";

// 地球儀表示
async function showEarth(canvas, region_id, country_id) {
    // サイズを指定
    const width = 640;
    const height = 640;

    renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true });
    renderer.setPixelRatio(window.devicePixelRatio);
    renderer.setSize(width, height);
    renderer.setClearColor(bg_color, 1.0);
    scene = new THREE.Scene();
    scene.fog = new THREE.Fog(bg_color, 100, 225);

    let dot_geo = new THREE.Geometry();
    dot_geo.vertices.push(new THREE.Vector3(0, 0, 0));

    // 座標取得API
    const res = await axios.get("/js/all.json");
    // 座標プロット
    points = plotPoints(res.data.coordinate, scene, dot_geo);

    // カメラ設定
    camera = new EarthCamera(45, width / height);
    if (typeof region_id == "number") {
        target = points.filter(p => p.matchRegion(region_id));
        camera.setRange(target);
        target.forEach(p => p.setPoint(notice_dot_style, 3));
        default_target = target;
    } else if (typeof country_id == "number") {
        target = points.filter(p => p.matchCountry(country_id));
        camera.setRange(target);
        default_target = target;
    }

    ticktack();

    $("#earth-wrap").removeClass("d-none");
}

function ticktack() {
    camera.animate();
    renderer.render(scene, camera);
    requestAnimationFrame(ticktack);
}

class EarthDot extends THREE.Points {
    constructor(geo, lon, lat, region, country) {
        super(geo, new THREE.PointsMaterial());
        this.lon = lon;
        this.lat = lat;
        this.region = region;
        this.country = country;
        this.position.x = 50 * Math.sin((Math.PI * lon) / 180) * Math.cos((Math.PI * lat) / 180);
        this.position.z = 50 * Math.cos((Math.PI * lon) / 180) * Math.cos((Math.PI * lat) / 180);
        this.position.y = 50 * Math.sin((Math.PI * lat) / 180);

        this.material.sizeAttenuation = false;
        this.material.fog = true;
        this.setPoint(default_dot_style, 2);
    }
    matchRegion(region_id) {
        return this.region.find(v => v == region_id) == region_id;
    }
    matchCountry(country_id) {
        return this.country.find(v => v == country_id) == country_id;
    }
    setPoint(style, dot_size) {
        this.material.color.setStyle(style);
        this.material.size = dot_size;
    }
}

class EarthCamera extends THREE.PerspectiveCamera {
    constructor(deg, aspect_ratio) {
        super(deg, aspect_ratio);
        this.move = this.defaultCamera;
    }

    animate() {
        const limit = 1000;
        const cur = Date.now();
        const coord = this.move();
        const past = cur - this.start;
        if (limit > past) {
            this.longitude = (coord.lon * past + this.lon_old * (limit - past)) / limit;
            this.latitude = (coord.lat * past + this.lat_old * (limit - past)) / limit;
        } else {
            this.longitude = coord.lon;
            this.latitude = coord.lat;
        }

        this.position.x = 150 * Math.sin(this.longitude) * Math.cos(this.latitude);
        this.position.z = 150 * Math.cos(this.longitude) * Math.cos(this.latitude);
        this.position.y = 150 * Math.sin(this.latitude);

        this.lon_old = this.longitude;
        this.lat_old = this.latitude;

        this.lookAt(new THREE.Vector3(0, 0, 0));
    }

    defaultCamera() {
        const lon = (Date.now() / 8000) % (Math.PI * 2);
        return {
            lon: lon < Math.PI ? lon : lon - Math.PI * 2,
            lat: (Math.sin(Date.now() / 12345) / 2) % Math.PI
        };
    }

    rangeCamera() {
        return {
            lon:
                ((this.lon_mid + (this.lon_size * 0.66 + 3) * Math.sin(Date.now() / 4000)) *
                    Math.PI) /
                180,
            lat:
                ((this.lat_mid + (this.lat_size * 0.66 + 3) * Math.cos(Date.now() / 4000)) *
                    Math.PI) /
                180
        };
    }

    // カメラの移動範囲
    setRange(points) {
        let camera = {
            type: 0,
            lon: [180, -180],
            lat: [90, -90]
        };
        let lon = [360, 0];
        points.forEach(p => {
            let t = p.lon < 0 ? 360 + p.lon : p.lon;
            if (t < lon[0]) {
                lon[0] = t;
            }
            if (t > lon[1]) {
                lon[1] = t;
            }
            if (p.lon < camera.lon[0]) {
                camera.lon[0] = p.lon;
            }
            if (p.lon > camera.lon[1]) {
                camera.lon[1] = p.lon;
            }
            if (p.lat < camera.lat[0]) {
                camera.lat[0] = p.lat;
            }
            if (p.lat > camera.lat[1]) {
                camera.lat[1] = p.lat;
            }
        });
        if (camera.lon[1] - camera.lon[0] > 180) {
            camera.lon[0] = lon[0];
            camera.lon[1] = lon[1];
        }

        this.lon_mid = (camera.lon[0] + camera.lon[1]) / 2;
        this.lat_mid = (camera.lat[0] + camera.lat[1]) / 2;
        this.lon_size = (camera.lon[1] - camera.lon[0]) / 2;
        this.lat_size = (camera.lat[1] - camera.lat[0]) / 2;
        this.move = points.length == 0 ? this.defaultCamera : this.rangeCamera;
        this.start = Date.now();
    }
}

function plotPoints(data, scene, dotGeometry) {
    let res = [];
    data.forEach(v => {
        let dot = new EarthDot(dotGeometry, v[0][0], v[0][1], v[1], v[2]);
        res.push(dot);
        scene.add(dot);
    });
    return res;
}

// 初期化
if ($("#earth").length == 1) {
    const param = location.pathname.split("/");
    let arg = [];
    param.forEach(v => {
        arg.push(v.match(/^\d+$/) ? Number(v) : null);
    });
    showEarth($("#earth").get(0), arg[1], arg[2]);
}

// 地域強調
$("body").on("mouseenter", "a.region", function() {
    const region_id = $(this).data("region-id");
    target = points.filter(p => p.matchRegion(region_id));
    target.forEach(p => p.setPoint(notice_dot_style, 3));
    camera.setRange(target);
});

// 国強調
$("body").on("mouseenter", "a.country", function() {
    const country_id = $(this).data("country-id");
    default_target.forEach(p => p.setPoint(default_dot_style, 2));
    target = points.filter(p => p.matchCountry(country_id));
    target.forEach(p => p.setPoint(notice_dot_style, 3));
    camera.setRange(target);
});

// 強調解除
$("body").on("mouseleave", "a", function() {
    if (default_target.length > 0) {
        default_target.forEach(p => p.setPoint(notice_dot_style, 3));
    } else {
        target.forEach(p => p.setPoint(default_dot_style, 2));
    }
    camera.setRange(default_target);
});
