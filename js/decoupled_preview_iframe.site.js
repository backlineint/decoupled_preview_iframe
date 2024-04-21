/**
 * @file
 * Site preview.
 */

(function (window, Drupal, drupalSettings) {
  Drupal.behaviors.decoupledPreviewIframeLoad = {
    attach(context) {
      const { selector } = drupalSettings.decoupled_preview_iframe;
      const iframe = context.querySelector(selector);

      if (!iframe) {
        return;
      }

      iframe.addEventListener('load', () => {
        iframe.classList.add('ready');
      });
    },
  };

  Drupal.behaviors.decoupledPreviewIframeLoadSyncRoute = {
    attach() {
      window.addEventListener(
        'message',
        (event) => {
          const { routeSyncType = 'DECOUPLED_PREVIEW_IFRAME_ROUTE_SYNC' } =
            drupalSettings.decoupled_preview_iframe;
          const { data } = event;

          if (data.type !== routeSyncType || !data.path) {
            return;
          }

          if (window.location.pathname !== data.path) {
            window.location.href = data.path;
          }
        },
        false,
      );
    },
  };
})(window, Drupal, drupalSettings);
