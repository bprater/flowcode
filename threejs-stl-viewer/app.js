import * as THREE from 'three';
import { STLLoader } from 'three/addons/loaders/STLLoader.js';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

let scene, camera, renderer, controls, mesh;
const loadingIndicator = document.getElementById('loading-indicator');

function init() {
    // Scene
    scene = new THREE.Scene();
    scene.background = new THREE.Color(0x222222);

    // Camera
    camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
    camera.position.set(50, 50, 50);

    // Renderer
    renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setSize(window.innerWidth, window.innerHeight);
    document.body.appendChild(renderer.domElement);

    // Lights
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
    scene.add(ambientLight);

    const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
    directionalLight.position.set(1, 1, 1).normalize();
    scene.add(directionalLight);

    // Controls
    controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.05;
    controls.screenSpacePanning = false;
    controls.minDistance = 10;
    controls.maxDistance = 500;

    // MODIFIED: Load the local 'object.stl'
    // This path is relative to index.html
    loadSTL('object7.stl');

    // Handle window resize
    window.addEventListener('resize', onWindowResize, false);

    // Start animation loop
    animate();
}

function loadSTL(url) {
    if (loadingIndicator) loadingIndicator.style.display = 'block';
    const loader = new STLLoader();
    loader.load(
        url,
        function (geometry) {
            if (loadingIndicator) loadingIndicator.style.display = 'none';

            geometry.computeBoundingBox();
            const boundingBox = geometry.boundingBox;
            const center = new THREE.Vector3();
            boundingBox.getCenter(center);
            geometry.translate(-center.x, -center.y, -center.z);

            const size = new THREE.Vector3();
            boundingBox.getSize(size);
            const maxDim = Math.max(size.x, size.y, size.z);
            const desiredSize = 50;
            const scale = desiredSize / maxDim;

            const material = new THREE.MeshStandardMaterial({
                color: 0x0077ff, // Changed color for variety
                metalness: 0.1,
                roughness: 0.75,
            });

            if (mesh) scene.remove(mesh);
            mesh = new THREE.Mesh(geometry, material);
            mesh.scale.set(scale, scale, scale);
            scene.add(mesh);

            controls.target.set(0, 0, 0);
            camera.lookAt(0, 0, 0);
            controls.update();

            console.log("STL loaded:", url);
        },
        (xhr) => {
            const percentLoaded = (xhr.loaded / xhr.total) * 100;
            console.log(percentLoaded + '% loaded');
            if (loadingIndicator) loadingIndicator.textContent = `Loading STL: ${Math.round(percentLoaded)}%`;
        },
        (error) => {
            if (loadingIndicator) loadingIndicator.textContent = 'Error loading STL!';
            console.error('An error happened while loading the STL from', url, ':', error);
        }
    );
}

function onWindowResize() {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
}

function animate() {
    requestAnimationFrame(animate);
    controls.update();
    renderer.render(scene, camera);
}

init();