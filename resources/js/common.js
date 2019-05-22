require('./bootstrap');

$('[data-toggle="popover"]').popover();

$(window).on('load', function () {
    resizeMaps();
    $('body').find('.getmaps').eq(0).click();
});
$(window).resize(function () {
    resizeMaps();
});

function resizeMaps() {
    if ($('#weather-list').length > 0) {
        let w = $('#weather-list').width();
        $('#maps').closest('div').css({width: `${w}px`});
    }
}

$('body').on('click', '.getmaps', function () {
    let e = $(this).closest('.city-info');
    $('#map-name').html(`${e.data('name')} 周辺地図`);
    $('#maps').attr('src', `https://maps.google.com/maps?output=embed&z=15&q=${e.data('lat')},${e.data('lon')}`);
});
