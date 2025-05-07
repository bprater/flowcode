<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Three.js STL Viewer</title>
    <style>
        body { margin: 0; overflow: hidden; }
        canvas { display: block; }
        #loading-indicator {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 20px;
            color: white;
            background-color: rgba(0,0,0,0.7);
            padding: 10px 20px;
            border-radius: 5px;
            display: none; /* Hidden by default */
        }
    </style>
</head>
<body>
    <div id="loading-indicator">Loading STL...</div>

    <!-- Import maps polyfill -->
    <script async src="https://unpkg.com/es-module-shims@1.8.0/dist/es-module-shims.js"></script>

    <script type="importmap">
    {
        "imports": {
            "three": "https://unpkg.com/three@0.160.0/build/three.module.js",
            "three/addons/": "https://unpkg.com/three@0.160.0/examples/jsm/"
        }
    }
    </script>

    <!-- MODIFIED: Path to app.js -->
    <script type="module" src="threejs-stl-viewer/app.js"></script>
</body>
</html>