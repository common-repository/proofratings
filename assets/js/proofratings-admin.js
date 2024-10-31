(function ($) {
    $('.proofratings-color-input').wpColorPicker();

    const btn_suport = $('.btn-support').get(0);
    tippy(btn_suport, { content: 'Need Help?' });

    $('.btn-cancel-subscription').on('click', function (e) {
        e.preventDefault();

        Swal.fire({
            icon: 'info',
            text: 'Are you sure you want to cancel your subscription? Cancellation will be effective immediately upon confirmation.',
            showDenyButton: true,
            confirmButtonText: 'Yes I\'m sure',
            denyButtonText: `No don't cancel`,
        }).then((result) => {
            if (result.isConfirmed) {
                window.location = $(this).attr('href');
            }
        })
    })


    $('.proofratings-customer-card .button-discard').on('click', function (e) {
        e.preventDefault();
        $('.proofratings-customer-card').removeClass('proofratings-customer-card-editing');
        $('.proofratings-customer-card').find('.button-primary').removeClass('inprogress')
    })

    $('.proofratings-customer-card .button-card-form').on('click', function (e) {
        e.preventDefault();
        $('.proofratings-customer-card').addClass('proofratings-customer-card-editing');
    })

    const card_container = $('.proofratings-customer-card .card-form');

    card_container.find('.card-number').mask('0000 0000 0000 0000');

    card_container.find('.card-expiry').mask('00 / 00', { placeholder: "MM / YY" });

    card_container.on('submit', function (event) {
        let is_valid = true;

        const submit_button = $(this).closest('.proofratings-customer-card').find('.button-primary')
        if (submit_button.hasClass('inprogress')) {
            return false;
        }

        $(this).find('.card-number').validateCreditCard(function (result) {
            is_valid = result.valid
        });

        const current_month = new Date().getMonth() + 1;
        const current_year = new Date().getFullYear().toString().substring(2);

        let card_expiry = $(this).find('.card-expiry').cleanVal().match(/.{1,2}/g)
        if (!Array.isArray(card_expiry)) {
            card_expiry = [];
        }

        if (typeof card_expiry[0] === 'undefined') {
            card_expiry[0] = 13;
        }

        if (typeof card_expiry[1] === 'undefined') {
            card_expiry[1] = current_year - 1
        }

        card_expiry = card_expiry.map(i => parseInt(i));


        if (card_expiry[0] > 12) {
            is_valid = false;
        }

        if (card_expiry[1] < current_year) {
            is_valid = false;
        }

        if (card_expiry[0] < current_month && card_expiry[1] <= current_year) {
            is_valid = false;
        }

        const cvc = $(this).find('.card-cvc').cleanVal();
        if (cvc.length < 3) {
            is_valid = false;
        }

        if (is_valid === false) {
            Swal.fire('Your card is not valid');
            return false;
        }

        submit_button.addClass('inprogress')

        $.post(proofratings_admin.ajax_url, { action: 'proofratings_update_payment_method', number: $(this).find('.card-number').cleanVal(), expiry: card_expiry.join(''), cvc: cvc }, function (response) {
            if (response?.success == true) {
                return window.location.reload();
            }

            Swal.fire(response?.error);
        }).always(() => {
            submit_button.removeClass('inprogress')
        })

        return false;
    })

})(jQuery)