(function ($) {
    function handle_proofrating_user_interaction(data) {
        $.post(proofratings.api + '/stats', { site_url: proofratings.site_url, ...data })
    }

    $('.proofratings-badge.baddddge-float').on('click', function (e) {
        $(this).addClass('opened');
    })

    let proofratings_event = null, event_timer = null;

    $('.proofratings-badge.badge-float').on("click", function (event) {
        const current = $(this);
        proofratings_event = event;

        event_timer = setTimeout(function () {
            if ( proofratings_event.detail == 1) {
                current.addClass('opened');
            }
        }, 300);

    }).on("dblclick", function (event) {
        clearTimeout(event_timer);
        proofratings_event = event;
    });

    $('.proofratings-badge .proofratings-close').on('click', function (e) {
        e.stopPropagation();

        container = $(this).closest('.proofratings-badge');

        const location_id = container.data('location');
        const badge_type = container.data('type');
        if (Cookies) {
            Cookies.set(`proofratings_badge_${badge_type}_${location_id}`, true)
        }

        container.fadeOut(120, function () {
            $(this).remove();
        });

        handle_proofrating_user_interaction({ type: 'engagement', id: location_id })
    })

    $('.proofratings-badges-popup .proofrating-close').on('click', function () {
        container = $('.proofratings-badge').removeClass('opened');
        handle_proofrating_user_interaction({ type: 'engagement', id: container.data('location') })
    })

    $('.proofratings-banner-badge .proofratings-banner-close').on('click', function (e) {
        e.stopPropagation();
        e.preventDefault();

        container = $(this).closest('.proofratings-banner-badge');

        container.fadeOut(100, function () {
            $(this).remove();
        })

        handle_proofrating_user_interaction({ type: 'engagement', id: container.data('location') })
    })

    last_scroll = 0;
    $(window).on('scroll', function () {
        current_position = $(window).scrollTop();

        going_down = current_position > last_scroll;
        last_scroll = current_position;

        if (going_down) {
            return $('.proofratings-banner-badge').addClass('going-down');
        }

        $('.proofratings-banner-badge').removeClass('going-down')
    })

    const proofratings_items = $('.proofratings-widget, .proofratings-badge, .proofratings-banner-badge');


    const location_ids = [];

    proofratings_items.each(function () {
        get_location = $(this).data('location');
        if (!location_ids.includes(get_location)) {
            location_ids.push(get_location);
            handle_proofrating_user_interaction({ type: 'impression', id: $(this).data('location') })
        }
    })

    proofratings_items.on('click', function () {
        handle_proofrating_user_interaction({ type: 'click', id: $(this).data('location') })
    })

    proofratings_items.on('mouseenter', function () {
        handle_proofrating_user_interaction({ type: 'hover', id: $(this).data('location') })
    })

})(jQuery)