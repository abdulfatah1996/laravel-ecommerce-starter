// عارض ثلاثي الأبعاد خفيف لمنتج بقاعدة مميزة (مصباح). مجسّم برمجي بلا ملفات خارجية.
import * as THREE from 'three';

export function mountViewer(container, type = 'lamp', color = '#17483D') {
    const width = container.clientWidth || 400;
    const height = container.clientHeight || 400;
    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setSize(width, height);
    renderer.domElement.setAttribute('aria-label', 'عرض ثلاثي الأبعاد: اسحب للتدوير');
    renderer.domElement.style.touchAction = 'none';
    container.appendChild(renderer.domElement);

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(35, width / height, 0.1, 100);
    camera.position.set(0, 1.1, 6.2);
    camera.lookAt(0, 1, 0);

    scene.add(new THREE.HemisphereLight(0xffffff, 0xb8aa95, 1.1));
    const key = new THREE.DirectionalLight(0xffffff, 1.6);
    key.position.set(3, 5, 4);
    scene.add(key);

    const brass = new THREE.MeshStandardMaterial({ color: 0xb08a4e, metalness: 0.7, roughness: 0.35 });
    const body = new THREE.MeshStandardMaterial({ color: new THREE.Color(color), roughness: 0.55 });
    const shadeMat = new THREE.MeshStandardMaterial({ color: 0xf3ead7, roughness: 0.9, side: THREE.DoubleSide, emissive: 0xf0d9a8, emissiveIntensity: 0.35 });

    const group = new THREE.Group();
    // القاعدة: دوران ملف جانبي (lathe) على شكل جرة
    const profile = [[0, 0], [0.9, 0], [0.95, 0.08], [0.8, 0.25], [0.75, 0.6], [0.95, 1.0], [0.85, 1.35], [0.3, 1.6], [0.12, 1.75]]
        .map(([x, y]) => new THREE.Vector2(x, y));
    group.add(new THREE.Mesh(new THREE.LatheGeometry(profile, 48), body));
    const stem = new THREE.Mesh(new THREE.CylinderGeometry(0.05, 0.05, 0.5, 16), brass);
    stem.position.y = 1.95;
    group.add(stem);
    const shade = new THREE.Mesh(new THREE.CylinderGeometry(0.75, 1.15, 1.1, 48, 1, true), shadeMat);
    shade.position.y = 2.45;
    group.add(shade);
    const bulb = new THREE.PointLight(0xffd9a0, 6, 6);
    bulb.position.y = 2.3;
    group.add(bulb);
    group.position.y = -0.6;
    scene.add(group);

    let dragging = false;
    let lastX = 0;
    let velocity = 0.004;
    const down = (e) => { dragging = true; lastX = e.clientX; velocity = 0; };
    const move = (e) => { if (dragging) { group.rotation.y += (e.clientX - lastX) * 0.01; lastX = e.clientX; } };
    const up = () => { dragging = false; };
    renderer.domElement.addEventListener('pointerdown', down);
    window.addEventListener('pointermove', move);
    window.addEventListener('pointerup', up);

    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let raf = 0;
    const loop = () => {
        if (!dragging && !reduce) group.rotation.y += velocity || 0;
        renderer.render(scene, camera);
        raf = requestAnimationFrame(loop);
    };
    loop();

    return () => {
        cancelAnimationFrame(raf);
        window.removeEventListener('pointermove', move);
        window.removeEventListener('pointerup', up);
        renderer.dispose();
        renderer.domElement.remove();
    };
}
