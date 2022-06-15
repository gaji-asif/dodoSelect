$('a[href="#"]').click(function (event) {
    event.preventDefault();
    return false;
});

let browserWindowHeight = $(window).height();

const initializeSidebarSlimScroll = elementHeight => {
    $('#__sidebarNavigationWrapper').slimScroll({
        height: `${elementHeight}px`,
        size: '5px'
    });
};

initializeSidebarSlimScroll(browserWindowHeight - 150);

$(window).on('resize', function () {
    browserWindowHeight = $(window).height();
    initializeSidebarSlimScroll(browserWindowHeight - 150);
});
