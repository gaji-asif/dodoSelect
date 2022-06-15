(function ($) {
    $.fn.doModal = function (state = 'close') {
        if (state === 'open' || state === 'show') {
            this.removeClass('modal-hide');
            $('body').addClass('modal-open');
        }

        if (state === 'close' || state === 'hide') {
            this.addClass('modal-hide');
            $('body').removeClass('modal-open');
        }

        return this;
    };
}(jQuery));

const modalBackToTop = () => {
    $('.modal-overflow').animate({
        scrollTop: '0'
    }, 'slow');
};
