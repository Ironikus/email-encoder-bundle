/* Email Encoder Bundle - Admin */
/*global jQuery*/
jQuery(function ($) {
    'use strict';

    var $encodeEmails = $('#encode_emails');

    $('#setting-error-settings_updated').click(function () {
        $(this).hide();
    });

    // enable/disable plain emails
    $('#encode_mailtos')
        .change(function () {
            var checked = $(this).prop('checked');

            $encodeEmails.attr('disabled', !checked);

            if (!checked) {
                $encodeEmails.attr('checked', false);
            }

            // force change trigger
            $encodeEmails.change();
        })
        .change();

    // show/hide notice
    $encodeEmails.change(function () {
        $('.notice-form-field-bug')[$encodeEmails.prop('checked') ? 'fadeIn' : 'fadeOut']();
    });

    // add form-table class to Encoder Form tables
    $('.eeb-form table').addClass('form-table');

    //
    $('.eeb-help-link').click(function (e) {
        $('#contextual-help-link').click();
        e.preventDefault();
    });

    // Workaround for saving disabled checkboxes in options db
    // prepare checkboxes before submit
    $('.wrap form').submit(function () {
        // force value 0 being saved in options
        $('*[type="checkbox"]:not(:checked)')
            .css({ 'visibility': 'hidden' })
            .attr({
                'value': '0',
                'checked': 'checked'
            });
    });

    // enable submit buttons
    $('*[type="submit"]')
        .attr('disabled', false)
        .removeClass('submit'); // remove class to fix button background

});
