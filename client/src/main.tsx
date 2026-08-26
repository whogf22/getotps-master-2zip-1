import { createRoot } from "react-dom/client";
import "./index.css";

if (!window.location.hash) {
  window.location.hash = "#/";
}

/**
 * Probe for a live animation loop *before* the app's modules evaluate.
 *
 * The landing page reveals every section with framer-motion, which drives
 * opacity 0 → 1 through the Web Animations API. Some in-app WebViews, heavily
 * throttled background tabs, and low-power mode never tick
 * requestAnimationFrame — there the animations are created but never advance,
 * hold their opening keyframe, and the page renders blank instead of merely
 * un-animated. A running WAAPI animation outranks author styles (including
 * `!important`), so this cannot be patched after render; components have to
 * skip their hidden initial state entirely.
 *
 * The flag is read at module scope by the page components, so App is imported
 * only after the probe resolves.
 */
function probeAnimationLoop(timeoutMs = 200): Promise<boolean> {
  return new Promise(resolve => {
    let ticks = 0;
    let running = true;
    const tick = () => {
      ticks++;
      if (running) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
    setTimeout(() => {
      running = false;
      resolve(ticks >= 2);
    }, timeoutMs);
  });
}

async function bootstrap() {
  const canAnimate = await probeAnimationLoop();
  (window as Window & { __NO_MOTION__?: boolean }).__NO_MOTION__ = !canAnimate;
  // Mirrored onto the root element so CSS can disable transitions too — a
  // stalled timeline freezes them at their `from` value (see index.css).
  if (!canAnimate) {
    document.documentElement.setAttribute("data-no-motion", "");
  }

  const { default: App } = await import("./App");
  createRoot(document.getElementById("root")!).render(<App />);
}

void bootstrap();
