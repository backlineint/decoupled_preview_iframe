/**
 * @file
 * Site preview.
 */

(function ($, window, Drupal, drupalSettings) {
  Drupal.behaviors.decoupledPreviewIframeLoad = {
    attach(context) {
      const { selector } = drupalSettings.decoupled_preview_iframe.node_view;
      const $iframe = $(selector, context);

      $iframe.on('load', () => {
        $iframe.addClass('ready');
      });
    },
  };

  Drupal.behaviors.decoupledPreviewIframeLoadSyncRoute = {
    attach() {
      window.addEventListener(
        'message',
        (event) => {
          const { routeSyncType = 'DECOUPLED_PREVIEW_IFRAME_ROUTE_SYNC' } =
            drupalSettings.decoupled_preview_iframe.node_view;
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
})(jQuery, window, Drupal, drupalSettings);
