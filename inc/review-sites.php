<?php

/**
 * Get connection site
 * @since  1.0.6
 */
function get_proofratings_review_sites() {
    return [
        'google' => [
            'theme_color' => '#03AB4E',
            'name' => __('Google', 'proofratings'),
            'title' => __('Google Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/google.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-google.png',
            'rating_title' => __('Google Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/google-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-google.svg',
            'category' => 'general'
        ],

        'facebook' => [
            'theme_color' => '#0f7ff3',
            'name' => __('Facebook', 'proofratings'),
            'title' => __('Facebook Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/facebook.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-facebook.png',
            'rating_title' => __('Facebook Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/facebook-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-facebook.svg',
            'category' => 'general'
        ],

        'yelp' => [
            'theme_color' => '#e21c21',
            'name' => __('Yelp', 'proofratings'),
            'title' => __('Yelp Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/yelp.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-yelp.png',
            'rating_title' => __('Yelp Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/yelp-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-yelp.svg',
            'category' => 'general'
        ],

        'bbb' => [
            'theme_color' => '#136796',
            'name' => __('BBB', 'proofratings'),
            'title' => __('BBB Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/bbb.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-bbb.png',
            'rating_title' => __('BBB Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/bbb-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-bbb.svg',
            'category' => 'general'
        ],

        'bestcompany' => [
            'theme_color' => '#3c5170',
            'name' => __('Best Company', 'proofratings'),
            'title' => __('Best Company Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/bestcompany.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-bestcompany.jpg',
            'rating_title' => __('Best Company Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/bestcompany-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-bestcompany.svg',
            'category' => 'general'
        ],

        'birdeye' => [
            'theme_color' => '#3c5170',
            'name' => __('Birdeye', 'proofratings'),
            'title' => __('Birdeye Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/birdeye.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-birdeye.png',
            'rating_title' => __('Birdeye Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/birdeye-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-birdeye.svg',
            'category' => 'general'
        ],

        //Home service review sites
        'angi' => [
            'theme_color' => '#FF5E4F',
            'name' => __('Angi', 'proofratings'),
            'title' => __('Angi Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/angi.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-angi.png',
            'rating_title' => __('Angi Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/angi-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-angi.svg',
            'category' => 'home-service'
        ],

        'guildquality' => [
            'theme_color' => '#032e57',
            'name' => __('GuildQuality', 'proofratings'),
            'title' => __('GuildQuality Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/guildquality.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-guildquality.png',
            'rating_title' => __('GuildQuality Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/guildquality-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-guildquality.svg',
            'category' => 'home-service'
        ],

        'buildzoom' => [
            'theme_color' => '#3D9EA0',
            'name' => __('Buildzoom', 'proofratings'),
            'title' => __('Buildzoom Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/buildzoom.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-buildzoom.png',
            'rating_title' => __('Buildzoom Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/buildzoom-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-buildzoom.svg',
            'category' => 'home-service'
        ],

        'homeadvisor' => [
            'theme_color' => '#3D9EA0',
            'name' => __('Homeadvisor', 'proofratings'),
            'title' => __('Homeadvisor Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/homeadvisor.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-homeadvisor.png',
            'rating_title' => __('Homeadvisor Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/homeadvisor-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-homeadvisor.svg',
            'category' => 'home-service'
        ],

        'houzz' => [
            'theme_color' => '#4DBC15',
            'name' => __('Houzz', 'proofratings'),
            'title' => __('Houzz Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/houzz.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-houzz.png',
            'rating_title' => __('Houzz Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/houzz-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-houzz.svg',
            'category' => 'home-service'
        ],

        //Solar review sites
        'energysage' => [
            'theme_color' => '#bf793f',
            'name' => __('EnergySage', 'proofratings'),
            'title' => __('EnergySage Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/energysage.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-energysage.png',
            'rating_title' => __('EnergySage Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/energysage-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-energysage.svg',
            'category' => 'solar'
        ],

        'solarreviews' => [
            'theme_color' => '#0f92d7',
            'name' => __('SolarReviews', 'proofratings'),
            'title' => __('SolarReviews Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/solarreviews.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-solarreviews.png',
            'rating_title' => __('SolarReviews Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/solarreviews-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-solarreviews.svg',
            'category' => 'solar'
        ],

        'solarquotes' => [
            'theme_color' => '#208ECD',
            'name' => __('Solarquotes', 'proofratings'),
            'title' => __('Solarquotes Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/solarquotes.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-solarquotes.png',
            'rating_title' => __('Solarquotes Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/solarquotes-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-solarquotes.svg',
            'category' => 'solar'
        ],

        'solartribune' => [
            'theme_color' => '#fbcb38',
            'name' => __('Solar Tribune', 'proofratings'),
            'title' => __('Solar Tribune Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/solartribune.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-solartribune.png',
            'rating_title' => __('Solar Tribune Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/solartribune-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-solartribune.svg',
            'category' => 'solar'
        ],

        'oneflare' => [
            'theme_color' => '#3D9EA0',
            'name' => __('Oneflare', 'proofratings'),
            'title' => __('Oneflare Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/oneflare.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-oneflare.png',
            'rating_title' => __('Oneflare Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/oneflare-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-oneflare.svg',
            'category' => 'solar'
        ],

        //SaaS/Software Review Sites
        'wordpress' => [
            'theme_color' => '#00769D',
            'name' => __('Wordpress', 'proofratings'),
            'title' => __('Wordpress Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/wordpress.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-wordpress.jpg',
            'rating_title' => __('Wordpress Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/wordpress-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-wordpress.svg',
            'category' => 'software'
        ],

        'capterra' => [
            'theme_color' => '#044D80',
            'name' => __('Capterra', 'proofratings'),
            'title' => __('Capterra Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/capterra.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-capterra.png',
            'rating_title' => __('Capterra Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/capterra-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-capterra.svg',
            'category' => 'software'
        ],

        'g2' => [
            'theme_color' => '#EF4D35',
            'name' => __('G2', 'proofratings'),
            'title' => __('G2 Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/g2.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-g2.png',
            'rating_title' => __('G2 Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/g2-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-g2.svg',
            'category' => 'software'
        ],

        'getapp' => [
            'theme_color' => '#41E3E2',
            'name' => __('Getapp', 'proofratings'),
            'title' => __('Getapp Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/getapp.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-getapp.png',
            'rating_title' => __('Getapp Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/getapp-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-getapp.svg',
            'category' => 'software'
        ],

        'softwareadvice' => [
            'theme_color' => '#FD810D',
            'name' => __('Software Advice', 'proofratings'),
            'title' => __('Software Advice Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/softwareadvice.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-softwareadvice.png',
            'rating_title' => __('Software Advice Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/softwareadvice-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-softwareadvice.svg',
            'category' => 'software'
        ],

        'saasworthy' => [
            'theme_color' => '#FEBA52',
            'name' => __('SaaSworthy', 'proofratings'),
            'title' => __('SaaSworthy Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/saasworthy.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-saasworthy.png',
            'rating_title' => __('SaaSworthy Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/saasworthy-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-saasworthy.svg',
            'category' => 'software'
        ],

        'crozdesk' => [
            'theme_color' => '#015BE3',
            'name' => __('Crozdesk', 'proofratings'),
            'title' => __('Crozdesk Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/crozdesk.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-crozdesk.png',
            'rating_title' => __('Crozdesk Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/crozdesk-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-crozdesk.svg',
            'category' => 'software'
        ],

        'quickbooks' => [
            'theme_color' => '#2C9F1C',
            'name' => __('Quickbooks', 'proofratings'),
            'title' => __('Quickbooks Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/quickbooks.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-quickbooks.png',
            'rating_title' => __('Quickbooks Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/quickbooks-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-quickbooks.svg',
            'category' => 'software'
        ],

        //Agency Review Sites
        'agencyspotter' => [
            'theme_color' => '#00769D',
            'name' => __('Agency Spotter', 'proofratings'),
            'title' => __('Agency Spotter Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/agencyspotter.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-agencyspotter.png',
            'rating_title' => __('Agency Spotter Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/agencyspotter-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-agencyspotter.svg',
            'category' => 'agency'
        ],

        'clutch' => [
            'theme_color' => '#00769D',
            'name' => __('Clutch', 'proofratings'),
            'title' => __('Clutch Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/clutch.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-clutch.png',
            'rating_title' => __('Clutch Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/clutch-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-clutch.svg',
            'category' => 'agency'
        ],

        'sortlist' => [
            'theme_color' => '#00769D',
            'name' => __('Sortlist', 'proofratings'),
            'title' => __('Sortlist Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/sortlist.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-sortlist.png',
            'rating_title' => __('Sortlist Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/sortlist-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-sortlist.svg',
            'category' => 'agency'
        ],

        'goodfirms' => [
            'theme_color' => '#00769D',
            'name' => __('Goodfirms', 'proofratings'),
            'title' => __('Sortlist Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/goodfirms.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-goodfirms.png',
            'rating_title' => __('Goodfirms Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/goodfirms-black.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-goodfirms.svg',
            'category' => 'agency'
        ],

        'nextdoor' => [
            'theme_color' => '#76d40e',
            'name' => __('Nextdoor', 'proofratings'),
            'title' => __('Nextdoor Review Settings', 'proofratings'),
            'logo' => PROOFRATINGS_PLUGIN_URL . '/assets/images/nextdoor.svg',
            'icon' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon-nextdoor.svg',
            'rating_title' => __('Goodfirms Rating', 'proofratings'),
            'icon2' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon2-nextdoor.svg',
            'icon3' => PROOFRATINGS_PLUGIN_URL . '/assets/images/icon3-nextdoor.svg',
            'category' => 'agency'
        ]
    ];
}