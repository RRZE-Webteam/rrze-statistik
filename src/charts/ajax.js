/**
 * Preserve WordPress's existing server-rendered settings and reload workflow.
 * @param {Function} $ WordPress jQuery.
 * @returns {void}
 */
function initializeSettings($) {
    let selector = '';
    /** Open the server-rendered settings. @returns {boolean} Cancel navigation. */
    function showSettings() {
        const button = $(this);
        selector = button.closest('[id^="rrze_statistik_widget"]').attr('id');
        $.ajax({
            url: window.ajaxurl, type: 'POST', data: {action: 'showform'},
            /** Display a loading indicator. @returns {void} */
            beforeSend() {
                button.hide().before('<span class="spinner" style="visibility:visible;display:block;margin:0 0 0 15px"></span>');
            },
            /** Insert settings markup. @param {string} html Server response. @returns {void} */
            success(html) {
                button.prev('.spinner').remove();
                $(document.getElementById(selector)).find('.inside').html(html);
            },
            /** Restore the configure control after failure. @returns {void} */
            error() { button.prev('.spinner').remove(); button.show(); },
        });
        return false;
    }
    /** Save options and reload affected widgets. @returns {boolean} Cancel submission. */
    function saveSettings() {
        const form = $(this);
        $.ajax({
            url: window.ajaxurl, type: 'POST', data: `${form.serialize()}&selector=${encodeURIComponent(selector)}`,
            /** Prevent repeated submissions. @returns {void} */
            beforeSend() {
                form.find('[type="submit"]').prop('disabled', true);
                form.find('.submit').append('<span class="spinner" style="display:inline-block;float:none;visibility:visible;margin:0 0 0 15px"></span>');
            },
            /** Rebuild the saved widget configuration. @returns {void} */
            success() { window.location.reload(); },
            /** Permit retry without losing form data. @returns {void} */
            error() { form.find('.spinner').remove(); form.find('[type="submit"]').prop('disabled', false); },
        });
        return false;
    }
    $(document).on('click', '[id^="rrze_statistik_widget"] .edit-box.open-box', showSettings);
    $(document).on('submit', '#rrze_statistik_settings', saveSettings);
}

if (window.jQuery) window.jQuery(initializeSettings);
