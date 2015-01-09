$(function() {
    //init foundation
    $(document).foundation();

    //add listeners
    $("a.scroll").click(function (event) {
        var scrollDown = 0;
        if ($(this.hash).offset().top > $(document).height() - $(window).height()) {
            scrollDown = $(document).height() - $(window).height();
        } else {
            scrollDown = $(this.hash).offset().top;
        }

        $('body').animate({
            scrollTop: scrollDown
        }, 1200, 'swing');
        event.preventDefault();
    });
});