Okay, based on our iterative development, here's a software specification summary for the "Three.js SVG on Card" application:

## Software Specification: Three.js SVG on Card Mapper

**1. Introduction & Overview**

The "Three.js SVG on Card Mapper" is a single-page web application designed to load, display, and manipulate Scalable Vector Graphics (SVG) images as textures on a 3D surface. This surface, referred to as a "card," is rendered using Three.js. The application allows users to specify an SVG source, adjust its position and scale on the card, and view the 3D object with a continuous subtle rotation. The primary goal is to explore mapping 2D vector graphics onto 3D objects with real-time user controls.

**2. Functional Requirements**

*   **FR1: SVG Loading:**
    *   FR1.1: The system shall allow users to input a URL for an SVG image.
    *   FR1.2: The system shall attempt to load the SVG data from the provided URL.
    *   FR1.3: For development and to bypass CORS issues, the system can be configured to load an SVG from the local filesystem (relative path to the HTML file).
*   **FR2: SVG URL Persistence:**
    *   FR2.1: The system shall store the last successfully loaded SVG URL (if loaded from a remote URL) in the browser's `localStorage`.
    *   FR2.2: On initialization, the system shall attempt to load the SVG from the persisted URL, if available.
*   **FR3: 3D Card Rendering:**
    *   FR3.1: The system shall render a 3D rectangular plane geometry (the "card") using Three.js.
    *   FR3.2: The card shall have a distinct base color.
*   **FR4: SVG Mapping to Card:**
    *   FR4.1: The system shall parse the loaded SVG data into drawable paths.
    *   FR4.2: The system shall render these SVG paths onto an offscreen 2D canvas.
    *   FR4.3: The offscreen canvas, containing the rendered SVG, shall be used as a `THREE.CanvasTexture`.
    *   FR4.4: This texture shall be applied to a separate 3D plane (`svgPlaneMesh`) positioned slightly in front of and aligned with the main card.
*   **FR5: Card Animation:**
    *   FR5.1: The system shall continuously animate the 3D card (and the overlaid SVG plane) with a gentle rotation on its X and Y axes to demonstrate the 3D effect.
*   **FR6: SVG Transformation Controls:**
    *   FR6.1: The system shall provide UI sliders for adjusting the X-offset of the SVG on the card.
    *   FR6.2: The system shall provide UI sliders for adjusting the Y-offset of the SVG on the card.
    *   FR6.3: The system shall provide UI sliders for adjusting the scale of the SVG on the card.
    *   FR6.4: The current values of these sliders shall be displayed to the user.
*   **FR7: Visual Styling:**
    *   FR7.1: The SVG rendering on the card shall have configurable fill and stroke colors (currently implemented with hardcoded debug colors: semi-transparent red fill, opaque green stroke).
    *   FR7.2: The stroke width of the rendered SVG shall be adjustable (currently set to a dynamic value based on scale for consistent visibility).
*   **FR8: User Feedback:**
    *   FR8.1: The system shall display the current SVG URL being processed or loaded.
    *   FR8.2: The system shall provide visual feedback (console logs, on-canvas error messages) for SVG loading errors, BBox calculation issues, or rendering problems.

**3. System Architecture & Components**

*   **3.1 Frontend:** Single HTML page (`.html` or `.php` as per user's environment).
    *   **Structure:** HTML5 for document layout and UI elements.
    *   **Styling:** Inline CSS for presentation.
    *   **Logic:** Embedded JavaScript.
*   **3.2 Rendering Engine:** Three.js (r128)
    *   Includes Scene, PerspectiveCamera, WebGLRenderer, PlaneGeometry, MeshBasicMaterial, MeshStandardMaterial, AmbientLight, DirectionalLight.
*   **3.3 SVG Processing:**
    *   `THREE.SVGLoader`: For fetching and parsing SVG data into `ShapePath` objects.
    *   **Bounding Box Calculation (`getRobustBBox`):**
        *   Primary method: Aggregates SVG path `d` attributes, creates a temporary hidden SVG element in the DOM, and uses the native `getBBox()` method for accuracy.
        *   Fallback method: If DOM `getBBox` fails or no `d` attributes, it tessellates `ShapePath` objects to calculate bounds.
    *   **SVG to Canvas Rendering (`renderSvgToCanvas`):**
        *   Iterates through `ShapePath` objects and their `subPaths` (which contain `Curve` objects).
        *   Uses HTML5 2D Canvas API (`Path2D` object) to draw each curve type:
            *   `LineCurve` -> `path2d.lineTo()`
            *   `QuadraticBezierCurve` -> `path2d.quadraticCurveTo()`
            *   `CubicBezierCurve` -> `path2d.bezierCurveTo()`
            *   `EllipseCurve` -> `path2d.ellipse()`
        *   Applies scaling and translation to fit the SVG (based on its BBox) onto the offscreen canvas with padding.
        *   Applies fill and stroke operations.
*   **3.4 Texture:** `THREE.CanvasTexture` generated from the offscreen 2D canvas.
*   **3.5 User Interface (UI) Elements:**
    *   Text input for SVG URL.
    *   "Load SVG" button.
    *   Display area for current SVG URL/status.
    *   Range sliders for X-offset, Y-offset, and Scale.
    *   Span elements to display current slider values.
    *   Main `div` container for Three.js renderer.
*   **3.6 State Management:**
    *   JavaScript variables for scene objects, texture data, current SVG URL.
    *   `localStorage` for persisting the SVG URL.

**4. Technical Implementation Details**

*   **Animation Loop:** `requestAnimationFrame` for smooth rendering and animation.
*   **Coordinate Systems:** Careful management of SVG coordinates, 2D canvas coordinates, and Three.js 3D world/local coordinates.
*   **Error Handling:** Console logs for various stages (loading, BBox, rendering). Visual error indicators on the canvas for critical failures (e.g., BBox errors, load failures).
*   **CORS:** Awareness of Cross-Origin Resource Sharing issues when loading remote SVGs, with local file loading as a workaround.
*   **Performance:** Offscreen canvas rendering is performed only when a new SVG is loaded or (potentially in future) if its styling changes. `texture.needsUpdate = true` signals Three.js to re-upload the texture.

**5. Future Considerations & Potential Enhancements**

*   **Advanced SVG Style Parsing:** Implement parsing of inline styles or `<style>` tags within the SVG for fill, stroke, stroke-width, etc., to use the SVG's native appearance.
*   **UI Color Pickers:** Add UI controls (e.g., color pickers) for users to dynamically change the fill and stroke colors of the rendered SVG, and the card color.
*   **Stroke Width Control:** UI slider for SVG stroke width.
*   **More Robust SVG Support:** Handle more complex SVG features (e.g., gradients, patterns, text elements – possibly by pre-rendering complex SVGs to a raster format before texture creation).
*   **Support for `fill="none"`:** Explicitly check for `fill="none"` from SVG styles and skip the fill operation on the canvas if specified.
*   **Selection of 3D Target Shapes:** Allow users to map SVGs onto other primitive shapes (Sphere, Cube, Cylinder).
*   **Improved Error Display:** More user-friendly error messages on the main UI instead of just console/canvas debug.
*   **Optimization:** For very complex SVGs, investigate further optimizations in the rendering pipeline.

This specification reflects the application as developed through our conversation, highlighting its capabilities and the technical approaches taken.