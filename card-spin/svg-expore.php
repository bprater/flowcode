<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Three.js SVG on Card</title>
    <style>
        body {
            margin: 0;
            overflow: hidden;
            font-family: Arial, sans-serif;
            background-color: #282c34;
            color: #e6e6e6;
        }
        #container {
            width: 100vw;
            height: 100vh;
            display: block;
        }
        .controls {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: rgba(20, 20, 30, 0.85);
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            color: #e6e6e6;
            z-index: 10;
            max-width: 300px;
        }
        .controls div {
            margin-bottom: 12px;
        }
        .controls label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 0.9em;
        }
        .controls input[type="range"] {
            width: 100%;
            cursor: pointer;
        }
        .controls input[type="text"], .controls button {
            width: calc(100% - 18px);
            padding: 10px 8px;
            margin-top: 5px;
            border-radius: 4px;
            border: 1px solid #444;
            background-color: #333842;
            color: #e6e6e6;
            box-sizing: border-box;
        }
        .controls button {
            background-color: #61dafb;
            color: #20232a;
            cursor: pointer;
            font-weight: bold;
            border: none;
        }
        .controls button:hover {
            background-color: #4fa8c5;
        }
        #currentSvgUrl {
            font-size: 0.8em;
            color: #aaa;
            margin-top: 8px;
            word-break: break-all;
        }
        .slider-value {
            font-size: 0.9em;
            color: #bbb;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="controls">
        <div>
            <label for="svgUrlInput">SVG URL:</label>
            <input type="text" id="svgUrlInput" placeholder="Enter SVG URL here">
            <button id="loadSvgButton">Load SVG</button>
            <div id="currentSvgUrl">Loading...</div>
        </div>
        <div>
            <label for="svgOffsetX">Offset X: <span id="offsetXVal" class="slider-value">0.00</span></label>
            <input type="range" id="svgOffsetX" min="-1.5" max="1.5" step="0.01" value="0">
        </div>
        <div>
            <label for="svgOffsetY">Offset Y: <span id="offsetYVal" class="slider-value">0.00</span></label>
            <input type="range" id="svgOffsetY" min="-2" max="2" step="0.01" value="0">
        </div>
        <div>
            <label for="svgScale">Scale: <span id="scaleVal" class="slider-value">1.00</span></label>
            <input type="range" id="svgScale" min="0.1" max="3" step="0.01" value="1">
        </div>
    </div>

    <div id="container"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://unpkg.com/three@0.128.0/examples/js/loaders/SVGLoader.js"></script>

    <script>
        let scene, camera, renderer;
        let cardMesh, svgPlaneMesh;
        let svgTexture, offscreenCanvas, offscreenCtx;
        let currentSvgDataPaths = null;

        const CARD_WIDTH = 2.5;
        const CARD_HEIGHT = 3.5;
        const SVG_TEXTURE_SIZE = 512;

        const cardColor = new THREE.Color(0x999999);
        // --- MODIFIED COLORS ---
        const svgFillColorString = "rgba(255, 0, 0, 0.3)"; // Semi-transparent Red for fill
        const svgStrokeColorString = "rgba(0, 255, 0, 1)";   // Opaque Green for stroke
        // --- You can change these color strings directly ---

        const svgUrlInput = document.getElementById('svgUrlInput');
        const loadSvgButton = document.getElementById('loadSvgButton');
        const currentSvgUrlDiv = document.getElementById('currentSvgUrl');
        const svgOffsetXSlider = document.getElementById('svgOffsetX');
        const svgOffsetYSlider = document.getElementById('svgOffsetY');
        const svgScaleSlider = document.getElementById('svgScale');
        const offsetXValSpan = document.getElementById('offsetXVal');
        const offsetYValSpan = document.getElementById('offsetYVal');
        const scaleValSpan = document.getElementById('scaleVal');

        const DEFAULT_SVG_URL = 'football-helmet.svg';

        function init() {
            scene = new THREE.Scene();
            scene.background = new THREE.Color(0x282c34);
            camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
            camera.position.z = 5;
            const container = document.getElementById('container');
            renderer = new THREE.WebGLRenderer({ antialias: true });
            renderer.setPixelRatio(window.devicePixelRatio);
            renderer.setSize(window.innerWidth, window.innerHeight);
            container.appendChild(renderer.domElement);
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.7);
            scene.add(ambientLight);
            const directionalLight = new THREE.DirectionalLight(0xffffff, 0.9);
            directionalLight.position.set(5, 10, 7.5);
            scene.add(directionalLight);
            const cardGeometry = new THREE.PlaneGeometry(CARD_WIDTH, CARD_HEIGHT);
            const cardMaterial = new THREE.MeshStandardMaterial({ color: cardColor, side: THREE.DoubleSide, roughness: 0.6, metalness: 0.2 });
            cardMesh = new THREE.Mesh(cardGeometry, cardMaterial);
            scene.add(cardMesh);
            offscreenCanvas = document.createElement('canvas');
            offscreenCanvas.width = SVG_TEXTURE_SIZE;
            offscreenCanvas.height = SVG_TEXTURE_SIZE;
            offscreenCtx = offscreenCanvas.getContext('2d');
            loadSvgButton.addEventListener('click', () => {
                const url = svgUrlInput.value.trim();
                if (url) loadSVG(url); else alert("Please enter an SVG URL or file path.");
            });
            [svgOffsetXSlider, svgOffsetYSlider, svgScaleSlider].forEach(s => s.addEventListener('input', updateSvgTransform));
            window.addEventListener('resize', onWindowResize, false);
            svgUrlInput.value = DEFAULT_SVG_URL;
            loadSVG(DEFAULT_SVG_URL);
            animate();
        }

        async function getRobustBBox(svgPaths) {
            let combined_d = "";
            if (svgPaths && svgPaths.forEach) {
                svgPaths.forEach(path => {
                    if (path.userData && path.userData.node && path.userData.node.getAttribute) {
                        const d = path.userData.node.getAttribute('d');
                        if (d) combined_d += d + " ";
                    }
                });
            }

            if (!combined_d.trim()) {
                let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity, hasPoints = false;
                if (svgPaths && svgPaths.forEach) {
                    svgPaths.forEach(path => {
                        const shapes = THREE.SVGLoader.createShapes(path);
                        shapes.forEach(shape => {
                            const processPts = (pts) => {
                                if (pts && pts.length > 0) {
                                    hasPoints = true;
                                    pts.forEach(p => { minX=Math.min(minX,p.x); minY=Math.min(minY,p.y); maxX=Math.max(maxX,p.x); maxY=Math.max(maxY,p.y); });
                                }
                            };
                            processPts(shape.extractPoints(10).shape);
                            shape.holes.forEach(h => processPts(h.extractPoints(10).shape));
                        });
                    });
                }
                if (!hasPoints) return { x:0, y:0, width:100, height:100, error:"No points in fallback BBox" };
                const w = maxX-minX, h = maxY-minY;
                if (w <= 0 || h <= 0) return { x:minX, y:minY, width:w||100, height:h||100, error:"Zero/neg dimension in fallback BBox"};
                return { x:minX, y:minY, width:w, height:h };
            }

            const tempSvg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
            const tempPath = document.createElementNS("http://www.w3.org/2000/svg", "path");
            tempPath.setAttributeNS(null, "d", combined_d);
            tempSvg.appendChild(tempPath);
            Object.assign(tempSvg.style, {position:'absolute', visibility:'hidden', width:'0', height:'0'});
            let domBbox;
            try { document.body.appendChild(tempSvg); domBbox = tempPath.getBBox(); }
            catch(e) { console.error("DOM getBBox error:", e); domBbox = {x:0,y:0,width:100,height:100,error:"DOM getBBox failed"}; }
            finally { if (tempSvg.parentNode === document.body) document.body.removeChild(tempSvg); }
            return { x:domBbox.x, y:domBbox.y, width:domBbox.width, height:domBbox.height, ...(domBbox.error && {error:domBbox.error}) };
        }

        async function renderSvgToCanvas(svgPaths) {
            if (!svgPaths || svgPaths.length === 0) {
                console.warn("renderSvgToCanvas: No SVG data paths.");
                // ... (error indication for no paths)
                return;
            }
            
            offscreenCtx.clearRect(0,0,SVG_TEXTURE_SIZE,SVG_TEXTURE_SIZE);
            
            const bbox = await getRobustBBox(svgPaths);

            if(typeof bbox==='undefined' || !bbox || bbox.error || typeof bbox.width!=='number'||bbox.width<=0||typeof bbox.height!=='number'||bbox.height<=0||typeof bbox.x!=='number'||typeof bbox.y!=='number'){
                console.warn("Invalid BBox:", bbox ? JSON.parse(JSON.stringify(bbox)):bbox);
                // ... (error indication for invalid bbox)
                return;
            }
            const pad=0.05*SVG_TEXTURE_SIZE, tW=SVG_TEXTURE_SIZE-2*pad, tH=SVG_TEXTURE_SIZE-2*pad;
            const scale = Math.min(tW/bbox.width, tH/bbox.height);
            const tx = pad+(tW-bbox.width*scale)/2-bbox.x*scale;
            const ty = pad+(tH-bbox.height*scale)/2-bbox.y*scale;
            
            offscreenCtx.save();
            offscreenCtx.translate(tx,ty);
            offscreenCtx.scale(scale,scale);

            // Set styles for SVG paths
            offscreenCtx.fillStyle = svgFillColorString;
            offscreenCtx.strokeStyle = svgStrokeColorString;
            offscreenCtx.lineWidth = 1.5 / scale; // Adjusted line width

            for(const path of svgPaths){
                for(const subPath of path.subPaths){
                    if(!subPath.curves || subPath.curves.length===0 || !subPath.curves[0] || typeof subPath.curves[0].getPoint!=='function'){
                        console.warn("Skipping problematic subPath:", subPath); continue;
                    }
                    
                    const path2d = new Path2D();
                    const firstPt = subPath.curves[0].getPoint(0);
                    if(!firstPt){ console.error("CRIT: getPoint(0) failed for first curve!",subPath.curves[0]); continue; }
                    path2d.moveTo(firstPt.x, firstPt.y);

                    for(const curve of subPath.curves){
                        if(curve.isLineCurve){
                            if (curve.v2 && curve.v2.isVector2) path2d.lineTo(curve.v2.x,curve.v2.y);
                            else console.warn("Skip line: missing v2.", curve);
                        }
                        else if(curve.isQuadraticBezierCurve){
                            if (curve.v1 && curve.v1.isVector2 && curve.v2 && curve.v2.isVector2) 
                                path2d.quadraticCurveTo(curve.v1.x, curve.v1.y, curve.v2.x, curve.v2.y);
                            else console.warn("Skip quad curve: missing points.", curve);
                        } else if(curve.isCubicBezierCurve){
                            if (curve.v1 && curve.v1.isVector2 && curve.v2 && curve.v2.isVector2 && curve.v3 && curve.v3.isVector2)
                                path2d.bezierCurveTo(curve.v1.x, curve.v1.y, curve.v2.x, curve.v2.y, curve.v3.x, curve.v3.y);
                            else console.warn("Skip cubic curve: missing points.", curve);
                        } else if (curve.isEllipseCurve) {
                            if (typeof curve.aX === 'number' && typeof curve.aY === 'number' &&
                                typeof curve.xRadius === 'number' && typeof curve.yRadius === 'number' &&
                                typeof curve.aStartAngle === 'number' && typeof curve.aEndAngle === 'number') {
                                path2d.ellipse(
                                    curve.aX, curve.aY, curve.xRadius, curve.yRadius,
                                    curve.aRotation || 0, curve.aStartAngle, curve.aEndAngle,
                                    !(curve.aClockwise === true)
                                );
                            } else console.warn("Skip ellipse: missing params.", curve);
                        }
                        else console.warn("Unhandled curve type:", curve.type, curve);
                    }
                    offscreenCtx.fill(path2d);   // Re-enable fill
                    offscreenCtx.stroke(path2d); 
                }
            }
            offscreenCtx.restore();
            if(svgTexture) svgTexture.needsUpdate=true;
        }

        function loadSVG(url) {
            currentSvgUrlDiv.textContent = `Loading: ${url.substring(0,50)}...`;
            const loader = new THREE.SVGLoader();
            loader.load(url,
                async function(data){
                    console.log(`SVGLoader success ${url}. Paths:`,data.paths?data.paths.length:'null');
                    if(!data||!data.paths){ console.error("Invalid SVG data from:",url); return; }
                    currentSvgUrlDiv.textContent=`Loaded: ${url.substring(0,50)}...`;
                    currentSvgDataPaths=data.paths;
                    await renderSvgToCanvas(currentSvgDataPaths);
                    if(!svgPlaneMesh){
                        svgTexture=new THREE.CanvasTexture(offscreenCanvas);
                        svgTexture.minFilter=THREE.LinearFilter; svgTexture.magFilter=THREE.LinearFilter;
                        const geom=new THREE.PlaneGeometry(CARD_WIDTH,CARD_HEIGHT);
                        const mat=new THREE.MeshBasicMaterial({map:svgTexture,transparent:true,alphaTest:0.05,side:THREE.DoubleSide});
                        svgPlaneMesh=new THREE.Mesh(geom,mat);
                        svgPlaneMesh.position.z=0.011;
                        scene.add(svgPlaneMesh);
                        console.log("svgPlaneMesh created.");
                    }
                    updateSvgTransform();
                },
                (xhr)=>{ currentSvgUrlDiv.textContent=`Loading: ${url.substring(0,30)}... (${xhr.total?Math.round(xhr.loaded/xhr.total*100):0}%)`; },
                (err)=>{ 
                    console.error(`SVGLoader error ${url}`,err); 
                    currentSvgUrlDiv.textContent = `Error loading: ${url}. Check file path & console.`;
                    offscreenCtx.clearRect(0, 0, SVG_TEXTURE_SIZE, SVG_TEXTURE_SIZE);
                    offscreenCtx.fillStyle = 'rgba(255,0,0,0.7)';
                    offscreenCtx.fillRect(0,0, SVG_TEXTURE_SIZE, SVG_TEXTURE_SIZE);
                    offscreenCtx.fillStyle = 'white';
                    offscreenCtx.textAlign = 'center';
                    offscreenCtx.fillText("Load Error", SVG_TEXTURE_SIZE/2, SVG_TEXTURE_SIZE/2);
                    if(svgTexture) svgTexture.needsUpdate = true;
                }
            );
        }

        function updateSvgTransform(){
            if(!svgPlaneMesh)return;
            const ox=parseFloat(svgOffsetXSlider.value),oy=parseFloat(svgOffsetYSlider.value),sc=parseFloat(svgScaleSlider.value);
            offsetXValSpan.textContent=ox.toFixed(2); offsetYValSpan.textContent=oy.toFixed(2); scaleValSpan.textContent=sc.toFixed(2);
            svgPlaneMesh.position.set(ox,oy,svgPlaneMesh.position.z); svgPlaneMesh.scale.set(sc,sc,1);
        }
        function onWindowResize(){
            camera.aspect=window.innerWidth/window.innerHeight; camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth,window.innerHeight);
        }
        function animate(){
            requestAnimationFrame(animate);
            const t=Date.now()*0.0005;
            if(cardMesh){cardMesh.rotation.x=Math.sin(t*0.6)*0.25; cardMesh.rotation.y=Math.cos(t*0.4)*0.35;}
            if(svgPlaneMesh&&cardMesh)svgPlaneMesh.rotation.copy(cardMesh.rotation);
            renderer.render(scene,camera);
        }
        init();
    </script>
</body>
</html>