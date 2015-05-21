/**
 * This object contains all functionality for the Dashboard
 *
 * @since {{VERSION}}
 */
var CD_Dashboard;
(function ($) {
    CD_Dashboard = {

        /**
         * Initialization for Dashboard.
         *
         * @since {{VERSION}}
         */
        init: function () {

            // Ensure only one of each "single" widget instance is in the sidebar
            $('body.cd-dashboard').find('.widgets-sortables').sortable({
                update: function( e, ui ) {

                    var $new_widget = $(ui['item'][0]),
                        id_base = $new_widget.find('input[name="id_base"]').val();

                    if ($new_widget.find('input[name="cd_single"]').length) {

                        if ($(this).find('input[name="id_base"][value="' + id_base + '"]').length > 1) {
                            $new_widget.remove();
                            $(this).closest('.widgets-holder-wrap').effect('shake', 300);
                        }
                    }
                }
            });
        }
    };

    // Launch on ready
    $(function () {
        CD_Dashboard.init();
    });
})(jQuery);