jQuery(function ($) {
    let selector = "";
    // the Configure link click event
    $('[id^="rrze_statistik_widget"] .edit-box.open-box').click(function () {
        var button = $(this);
        selector = $(this).parent().parent().parent().parent().attr("id");
        $.ajax({
            url: ajaxurl, // it is predefined in /wp-admin/
            type: "POST",
            data: "action=showform",
            beforeSend: function (xhr) {
                // add preloader
                button
                    .hide()
                    .before(
                        '<span class="spinner" style="visibility:visible;display:block;margin:0 0 0 15px"></span>'
                    );
            },
            success: function (data) {
                // remove preloader
                button.prev().remove();
                // insert settings form
                $("#" + selector)
                    .find(".inside")
                    .html(data);
            },
        });
        return false;
    });

    // form submit event
    $("body").on("submit", "#rrze_statistik_settings", function () {
        var form = $(this);
        $.ajax({
            url: ajaxurl,
            type: "POST",
            data: $(this).serialize() + `&selector=${selector}`, // all form fields
            beforeSend: function (xhr) {
                // add preloader just after the submit button
                form.find(".submit").append(
                    '<span class="spinner" style="display:inline-block;float:none;visibility:visible;margin:0 0 0 15px"></span>'
                );
            },
            success: function (data) {
                $('[id^="rrze_statistik_widget"]').find(".inside").html(data);
                // show the Configure link again
                $('[id^="rrze_statistik_widget"] .edit-box.open-box').show();
                window.location.reload();
            },
        });
        return false;
    });
});
