import runtime from 'serviceworker-webpack-plugin/lib/runtime';

function registerServiceWorker() {
  document.addEventListener("DOMContentLoaded", () => {
    if (!('serviceWorker' in navigator)) {
      console.warn("Service workers are not supported by this browser");
      return;
    }
     runtime.register();
  });
}

export default registerServiceWorker;