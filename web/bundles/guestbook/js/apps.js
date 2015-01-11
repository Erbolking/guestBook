$(function() {
    //init foundation
    $(document).foundation();

    //add listeners
    var previousPosition, previousEntryId;
    $("a.scroll").click(function (e) {
        var scrollDown = 0;
        if ($(this.hash).offset().top > $(document).height() - $(window).height()) {
            scrollDown = $(document).height() - $(window).height();
        } else {
            scrollDown = $(this.hash).offset().top;
        }
        //scroll down
        $('body').animate({
            scrollTop: scrollDown
        }, 1200, 'swing');

        //render reply block
        var username = $(this).data('username');

        var id = $(this).data('id');
        previousPosition = e.pageY - 100;
        previousEntryId = id;
        if (username) {
            $('#reply-name').html(username);
            $('#form_parent').val(id);
            $('.reply-block').show();
        }
        e.preventDefault();
    });

    $('#perPage').change(function() {
        location.href = '?limit=' + $(this).val();
    });

    $('#break-reply').click(function(e) {
        //hide reply block
        $('form_parent').val('');
        $('.reply-block').hide();

        var entityBlock = $('#' + previousEntryId);
        entityBlock.css('background', 'rgb(255, 255, 196)');
        setTimeout(function () {
            entityBlock.css('background', 'none');
        }, 1000);
        //scroll up
        if (previousPosition) {
            $('body').animate({
                scrollTop: previousPosition
            }, 400, 'swing');
        }
        e.preventDefault();
    });
});