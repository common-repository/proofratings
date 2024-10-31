(function ($) {
    $('#proofrating-notice').on('click', 'a', function(e){
        if ( $(this).attr('target') != '_blank' ) {
            e.preventDefault();
        }
        
        $(this).closest('#proofrating-notice').hide();

        jQuery.post(proofratingsDashboard.ajaxurl, {action: 'proofratings_notice_feedback', days: $(this).data('days')})
    })
    
})(jQuery)