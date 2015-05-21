/**
 * This object contains all functionality for the Dashboard
 *
 * @since {{VERSION}}
 *
 * @global jQuery
 * @global CD_l18n
 */
var CD_Dashboard;
(function ($, l18n) {
    CD_Dashboard = {

        /**
         * Initialization for Dashboard.
         *
         * @since {{VERSION}}
         */
        init: function () {

            // Ensure only one of each "single" widget instance is in the sidebar
            $('body.cd-dashboard').find('.widgets-sortables:not(#wp_inactive_widgets)').sortable({
                update: function( e, ui ) {

                    var $new_widget = $(ui['item'][0]),
                        id_base = $new_widget.find('input[name="id_base"]').val(),
                        $description = $(this).find('.sidebar-description');

                    $description.find('.error').remove();

                    if ($new_widget.find('input[name="cd_single"]').length) {

                        if ($(this).find('input[name="id_base"][value="' + id_base + '"]').length > 1) {
                            $new_widget.remove();
                            $(this).closest('.widgets-holder-wrap').effect('shake', {
                                distance: 10,
                                times: 2
                            }, 300);
                            $description.append('<div class="error"><p>' + l18n['one_widget_per_sidebar'] + '</p></div>');
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
})(jQuery, CD_l18n);