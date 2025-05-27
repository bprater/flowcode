# 3D Baseball Card Visualizer - Application Summary

## 1. Core Functionality

The application displays an interactive 3D baseball card using Three.js. The card features:
- A front face with a customizable image and text fields (Player Name, Position).
- A solid colored back.
- The card continuously oscillates around its Y-axis, primarily showing the front.
- Users can click and drag the card to spin it freely. Upon release, the card exhibits inertial movement and then smoothly "springs" back to its default oscillating motion.

## 2. Key Features & Controls

The application provides two main UI panels for customization:

**A. Presets Panel (Left Side):**
- **Load/Save Presets:** Users can save their current card configuration (including all visual styles, text, and interaction settings) as a named preset.
  - Preset names are automatically generated based on the "Player Name" text input, with automatic numbering (e.g., "Player Name (2)") to handle duplicates.
- **Persistent Storage:** Presets are saved to the browser's `localStorage`, persisting across sessions.
- **Apply Presets:** Clicking a saved preset in the list instantly applies its settings to the card and updates all UI controls. The selected preset is highlighted.
  - Applying a preset triggers a brief "pop" animation on the card.
- **Delete Presets:** Individual presets can be deleted.
- **Clear All Presets:** A button allows users to remove all saved presets.

**B. Controls Panel (Right Side):**

The controls are organized into logical groups:

**I. Card Content:**
- **Image:** Dropdown to select from ~10 predefined sports-themed images.
- **Player Name:** Text input for the player's name (updates live).
- **Position:** Text input for the player's position (updates live).
- **Card BG Color:** Color picker for the front face background color of the card.

**II. Player Name Styling:**
- **Font Family:** Dropdown to select from ~15 common font families.
- **Font Size:** Slider to adjust the font size (as a factor of texture height).
- **Letter Spacing:** Slider to adjust letter spacing (in pixels on the texture).
- **Font Color:** Color picker for the player name text.
- **Y Offset:** Slider to adjust the vertical position of the player name text.

**III. Position Styling:**
- **Font Family:** Dropdown for font family.
- **Font Size:** Slider for font size factor.
- **Letter Spacing:** Slider for letter spacing.
- **Font Color:** Color picker for position text.
- **Y Offset:** Slider to adjust the vertical position of the position text.

**IV. Interaction Physics:**
- **Drag Sensitivity:** Slider to control how responsive the card is to mouse dragging.
- **Spin Damping:** Slider to control how quickly the inertial spin (after a drag) slows down.
- **Return Easing Rate:** Slider to control the speed/strength of the spring-back mechanism when the card returns to its oscillating path.
- **Return Easing Algorithm:** Dropdown to select from various standard easing functions (e.g., `easeOutCubic`, `linear`) that influence the *feel* of the return spring (currently, the "Return Easing Rate" is the primary control for the spring strength, with the selected function being available for more advanced future integration).

**V. Card Oscillation:**
- **Osc. Angle (°):** Slider to control the maximum angle (in degrees) of the card's left-right swing during its default oscillation.
- **Osc. Speed:** Slider to control the speed of this continuous oscillation.

**VI. General Actions:**
- **Randomize Card Button:**
  - Sets random text for Player Name and Position.
  - Selects a random image from the predefined list.
  - Randomizes all visual styling options: card background color, font families, sizes, colors, letter spacing, and Y-offsets for both name and position.
  - Randomizes interaction physics and oscillation parameters.
  - Updates all UI controls to reflect the new randomized state.

## 3. Technical Implementation Details

- **Rendering:** Three.js (r128 via CDN) for 3D rendering.
- **Card Geometry:** `THREE.BoxGeometry` with a slight depth.
- **Card Face Texture:**
  - Dynamically generated using a 2D `<canvas>` element.
  - The image, background color, and text (with all styling options) are drawn onto this canvas.
  - The canvas is then used as a `THREE.CanvasTexture` for the front material of the card.
- **Materials:** `THREE.MeshStandardMaterial` for physically-based rendering appearance, allowing interaction with scene lighting.
- **Animation Loop (`requestAnimationFrame`):**
  - Handles continuous oscillation of the `targetReturnRotation`.
  - Manages user drag input and calculates inertial `rotationVelocity`.
  - Applies damping to `rotationVelocity`.
  - Implements a spring-like force to pull `cardMesh.rotation` towards the `targetReturnRotation`.
  - Snaps the card to the target and zeroes out velocity when settled.
- **Image Handling:**
  - A single `Image` object (`currentCardImageObject`) is reused for loading images to optimize.
  - A "Loading..." placeholder can appear on the card if a new image is being fetched.
- **State Management:**
  - A global `config` object holds most of the customizable settings.
  - `currentPlayerName` and `currentPlayerPosition` store the live text values.
  - `controlElementsConfig` object maps UI element IDs to their corresponding `config` properties and update logic, facilitating UI synchronization.
- **Event Handling:** Standard DOM event listeners for UI controls (`input`, `change`) and mouse interaction on the canvas (`mousedown`, `mouseup`, `mousemove`).
- **No External Libraries (beyond Three.js):** All logic for easing (conceptual), physics, and UI is custom.

## 4. Code Structure Highlights

- **`init()`:** Sets up the scene, camera, renderer, lighting, initial card, UI controls, and starts the animation loop.
- **`setupControls()`:** Initializes all UI elements, populates dropdowns, and attaches event listeners.
- **`updateControlsFromConfig()`:** Syncs UI element values with the current state of the `config` object and text variables.
- **`_drawContentToCanvas()`:** Central function for drawing all elements (background, image, text with styles) onto the off-screen canvas for the card texture.
- **`redrawCardFace()`:** Called when any visual aspect (text, style, color) changes *without* changing the main image. Re-uses `currentCardImageObject`.
- **`updateCardImageAndRedraw()`:** Called when the selected image URL changes. Loads the new image into `currentCardImageObject` and then triggers `redrawCardFace()`.
- **Preset Functions:** (`loadPresets`, `renderPresetsList`, `handleSavePreset`, `applyPreset`, `deletePreset`, `handleClearAllPresets`) manage the preset system and `localStorage` interaction.
- **`animate()`:** The main animation and physics update loop.

## 5. Potential Areas for Future Development

- **Advanced Easing Integration:** Fully integrate the selected easing algorithms into the spring-back mechanism by dynamically calculating the `t` (progress) value for the easing functions.
- **Custom Font Loading:** Allow users to use web fonts (e.g., Google Fonts) beyond the basic system fonts.
- **More Complex Card Geometry:** Rounded corners, embossing, etc.
- **Two-Sided Card:** Option to design and display the back of the card.
- **Error Handling:** More robust error handling for image loading failures.
- **Performance Optimization:** For very rapid texture updates if many controls are manipulated simultaneously.