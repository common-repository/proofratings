(function ($) {
    if (!$('#analytics-chart').length) {
        return;
    }

    const ctx = document.getElementById("analytics-chart").getContext("2d");

    const impression_gradient = ctx.createLinearGradient(0, 0, 0, 600);
    impression_gradient.addColorStop(0, "rgba(65, 35, 255, 0.5)");
    impression_gradient.addColorStop(1, "rgba(250,174,50,0)");

    const click_gradient = ctx.createLinearGradient(0, 0, 0, 600);
    click_gradient.addColorStop(0, "rgba(253, 190, 145, 0.5)");
    click_gradient.addColorStop(1, "rgba(250,174,50,0)");

    const hover_gradient = ctx.createLinearGradient(0, 0, 0, 600);
    hover_gradient.addColorStop(0, "rgba(76, 175, 80, 0.5)");
    hover_gradient.addColorStop(1, "rgba(76,175,80,0)");

    const analyticsChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: [],
            datasets: [
                {
                    label: "Impressions",
                    tension: 0.7,
                    data: [],
                    fill: "start",
                    backgroundColor: impression_gradient,
                    borderColor: "rgba(65, 35, 255, 0.7)",
                    borderWidth: 2,
                }, {
                    label: "Hover",
                    tension: 0.7,
                    data: [],
                    fill: "start",
                    backgroundColor: hover_gradient,
                    borderColor: "rgba(76, 175, 80, 0.7)",
                    borderWidth: 2,
                },
                {
                    label: "Click",
                    tension: 0.7,
                    data: [],
                    fill: "start",
                    backgroundColor: click_gradient,
                    borderColor: "rgba(253, 190, 145, 0.7)",
                    borderWidth: 2,
                }
            ],
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        maxTicksLimit: 12
                    },
                }
            },

            plugins: {
                legend: {
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 40,
                        boxHeight: 40,
                        padding: 50,
                        font: {
                            lineHeight: 90,
                        }
                    }
                }
            }
        },
    });


    const analytics_input = $("#analytics-date"), proofratings_analytics = (state, action) => ({ ...state, ...action.payload })

    const number_format = (x) => {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }


    const update_dashboard = (state, monthly = false) => {

        const get_sessions = () => {
            var now = state.start.clone(), sessions = [];

            const date_format = monthly ? 'YYYY-MM' : 'YYYY-MM-DD';
            const add_session = monthly ? 'months' : 'days';

            while (now.isSameOrBefore(state.end)) {
                sessions.push(now.format(date_format));
                now.add(1, add_session);
            }

            return sessions;
        };

        const sessoins = get_sessions();

        const santize_data = (data, date) => {
            get_item = data.find((stat) => stat.date == date);
            return get_item ? parseInt(get_item.result) : 0;
        }

        const clicks = sessoins.map((date) => santize_data((state.clicks || []), date))
        const hovers = sessoins.map((date) => santize_data((state.hovers || []), date));
        const impressions = sessoins.map((date) => santize_data((state.impressions || []), date));
        const conversions = sessoins.map((date) => santize_data((state.conversions || []), date));
        const engagements = sessoins.map((date) => santize_data((state.engagements || []), date));

        $(".analytics-information-custom .impressions .counter").html(number_format(impressions.reduce((a, b) => a + b, 0)));
        $(".analytics-information-custom .hovers .counter").html(number_format(hovers.reduce((a, b) => a + b, 0)));
        $(".analytics-information-custom .clicks .counter").html(number_format(clicks.reduce((a, b) => a + b, 0)));
        $(".analytics-information-custom .engagements .counter").html(number_format(engagements.reduce((a, b) => a + b, 0)));


        const total_conversions = conversions.reduce((a, b) => a + b, 0);
        $(".analytics-information-custom .conversions .counter").html(number_format(total_conversions));
        if (total_conversions > 0) {
            $(".analytics-information-custom .conversions").show();
        } else {
            $(".analytics-information-custom .conversions").hide()
        }

        $(".analytics-information-alltime .impressions .counter").html(number_format(state.total_impressions));
        $(".analytics-information-alltime .hovers .counter").html(number_format(state.total_hover));
        $(".analytics-information-alltime .clicks .counter").html(number_format(state.total_clicks));
        $(".analytics-information-alltime .engagements .counter").html(number_format(state.total_engagement));

        const alltile_conversions = parseInt(state?.total_conversions);
        $(".analytics-information-alltime .conversions .counter").html(alltile_conversions);
        if (alltile_conversions > 0) {
            $(".analytics-information-alltime .conversions").show();
        } else {
            $(".analytics-information-alltime .conversions").hide();
        }

        analytics_input.children("span").html(state.start.format("YYYY-MM-DD") + " ~ " + state.end.format("YYYY-MM-DD"));

        analyticsChart.data.labels = sessoins.map((date) => moment(date).format("DD MMM"));
        if (monthly) {
            analyticsChart.data.labels = sessoins.map((date) => moment(date).format("MMM YY"));
        }

        analyticsChart.data.datasets[0].data = impressions;
        analyticsChart.data.datasets[1].data = hovers;
        analyticsChart.data.datasets[2].data = clicks;

        analyticsChart.update();
    }

    const analytics_store = Redux.createStore(proofratings_analytics, {
        domain: '',
        impressions: [],
        hovers: [],
        clicks: [],
        conversion: [],
        engagements: [],
        start: moment().subtract(6, "days"),
        end: moment()
    })

    analytics_store.subscribe(() => {
        const state = analytics_store.getState();
        const { start, end, domain, location } = state;

        const monthly = (moment(new Date(end)).diff(new Date(start), 'months', true)) > 6;

        const data = { site_url: proofratings.site_url, monthly, domain, location, start: start.format("YYYY-MM-DD 00:00:00"), end: end.format("YYYY-MM-DD 23:59:59") }

        const request = $.get(proofratings.api + '/stats', data, (payload) => {
            update_dashboard({ ...state, ...payload }, monthly);
        });

        request.always(function () {
            $(".proofratings-analytics-wrap").removeClass("loading");
        });
    })

    $('.analytics-filter .location-filter').on('change', function () {
        analytics_store.dispatch({ type: 'UPDATE', payload: { location: $(this).val() } })
    })

    var start = analytics_store.getState().start, end = analytics_store.getState().end;

    const update_date_string = (start, end) => {
        analytics_store.dispatch({ type: 'UPDATE', payload: { start, end } })
    };

    analytics_input.daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
            "Last 7 Days": [moment().subtract(6, "days"), moment()],
            "This Month": [
                moment().startOf("month"),
                moment().endOf("month"),
            ],
            "Last Month": [
                moment().subtract(1, "month").startOf("month"),
                moment().subtract(1, "month").endOf("month"),
            ],
            "This Year": [
                moment().startOf("year"),
                moment().endOf("year"),
            ],
        },
    }, update_date_string);

    update_date_string(start, end);
})(jQuery);