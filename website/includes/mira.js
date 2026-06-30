/**
 * Changes the active canvas of the first Mirador window to a specified index.
 * * @param {Object} miradorInstance - The initialized Mirador viewer instance.
 * @param {number} targetIndex - The index of the canvas you want to switch to (0-indexed).
 */
function changeMiradorCanvasIndex(miradorInstance, targetIndex) {
  if (!miradorInstance || !miradorInstance.store) {
    console.error("Invalid Mirador instance provided.");
    return;
  }

  const store = miradorInstance.store;
  const state = store.getState();

  // 1. Get the first window ID dynamically
  const windowIds = Object.keys(state.windows);
  if (windowIds.length === 0) {
    console.error("No active Mirador windows found.");
    return;
  }
  const windowId = windowIds[0]; 
  const currentWindow = state.windows[windowId];
  const manifestId = currentWindow.manifestId;

  // 2. Safely retrieve the manifest's canvas data
  const manifestData = state.manifests[manifestId];
  if (!manifestData || !manifestData.json) {
    console.error("Manifest data not loaded or found in state.");
    return;
  }

  const manifestJson = manifestData.json;
  let canvases = [];

  if (manifestJson.sequences && manifestJson.sequences[0]) {
    // IIIF v2
    canvases = manifestJson.sequences[0].canvases || [];
  } else if (manifestJson.items) {
    // IIIF v3
    canvases = manifestJson.items || [];
  }

  // 3. Ensure the target index exists within bounds
  if (targetIndex < 0 || targetIndex >= canvases.length) {
    console.error(`Target index ${targetIndex} is out of bounds (0 to ${canvases.length - 1}).`);
    return;
  }

  const targetCanvas = canvases[targetIndex];
  const canvasId = targetCanvas.id || targetCanvas['@id'];

  if (!canvasId) {
    console.error("Could not resolve a valid canvas ID for index:", targetIndex);
    return;
  }

  // 4. Dispatch the exact payload structure Mirador's reducer requires
  store.dispatch({
    type: 'mirador/SET_CANVAS',
    windowId: windowId,
    canvasId: canvasId,
    visibleCanvases: [canvasId] // Crucial: Fixes the 'includes' of undefined error
  });
}


// listener for Mirador canvas changer
document.addEventListener('click', function (e) {
    const link = e.target.closest('.locus');
    if (!link) return;
//    e.preventDefault();
    changeMiradorCanvasIndex(viewer, parseInt(link.dataset.canvas, 10) - 1);
});