(function ($) {
  "use strict";

  // var $body = $('body');
  var $doc = $(document);
  var $win = $(window);

  $doc.on('tinymce-editor-init', function (event, editor) {
    tinymce.editors[0].on('dirty', function (ed, e) {
      $win.trigger('stampd-ext-wp-editors-dirty', e);
    });
  });

  $win.keypress(function (e) {
    if (e.target) {
      if (e.target.classList.contains('wp-editor-area') && e.target.id === 'content') {
        $win.trigger('stampd-ext-wp-editors-dirty', e);
      }
    }
  });

  $win.on('stampd-ext-wp-editors-dirty', function (e) {
    var $stamp_btn = $('#stampd_ext_wp_stamp_btn');
    var $post_changed_text = $('.js-stampd-post-changed');
    $stamp_btn.attr('disabled', true);
    $post_changed_text.show();
  });

})(jQuery);