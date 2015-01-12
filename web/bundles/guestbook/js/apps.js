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
            entityBlock.css('background', '');
        }, 1000);
        //scroll up
        if (previousPosition) {
            $('body').animate({
                scrollTop: previousPosition
            }, 400, 'swing');
        }
        e.preventDefault();
    });

    var postForm = $('#postForm');
    var options = {
        beforeSend: function () {
            postForm.fadeTo("slow", 0.5);
        },
        complete: function (object) {
            //remove all previous error labels
            $('.validation').remove();
            if (typeof object.responseJSON['errors'] !== 'undefined') {
                var errors = object.responseJSON['errors'];

                for (var error in errors) {
                    var errorHolder = $('<li></li>').html(errors[error]);
                    var errorsCollection = $('<ul></ul>').addClass('validation').append(errorHolder);
                    $('#form_' + error).after(errorsCollection);
                }
            }
            if (typeof object.responseJSON['status'] !== 'undefined' && object.responseJSON['status'] == 'ok') {
                var entry = object.responseJSON['entry'];

                postForm.trigger("reset");
                //change captcha
                $('img.captcha').trigger('click');

                //create element
            }
            postForm.fadeTo("slow", 1);
        }
    };
    postForm.submit(function(e) {
        e.preventDefault();
        postForm.ajaxSubmit(options);
    });

    var createNewEntry = function(properties, parent) {

    }

    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $('.scrollUp').fadeIn();
        } else {
            $('.scrollUp').fadeOut();
        }
    });

    $('.scrollUp').click(function () {
        $("html, body").animate({
            scrollTop: 0
        }, 800);
        return false;
    });

});