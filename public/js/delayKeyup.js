(function ($) {
    $.fn.delayKeyup = function (callback, ms) {
        return this.each(function () { // $(this) not necessary for a jQuery add-on
            let timer = 0;
            const elem = this;
            $(this).keyup(function () {
                clearTimeout(timer);
                timer = setTimeout(callback.bind(elem), ms);
            });
        });
    };
}(jQuery));
