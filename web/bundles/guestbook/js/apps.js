$(function() {
    //init foundation
    $(document).foundation();

    //add listeners
    var previousPosition, previousEntryId;
    $('#entries').on('click', 'a.scroll', function (e) {
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
        var name = $(this).data('name');

        var id = $(this).data('id');
        previousPosition = e.pageY - 100 - $(this).outerHeight();
        previousEntryId = id;
        if (name) {
            $('#reply-name').html(name);
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
        $('#form_parent').val('');
        $('.reply-block').hide();

        backLightEntry($('#' + previousEntryId));

        //scroll up
        if (previousPosition) {
            scrollTo(previousPosition);
        }
        e.preventDefault();
    });

    var postForm = $('#postForm');
    var options = {
        beforeSend: function () {
            postForm.fadeTo('slow', 0.5);
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
                var topScroll = 0;

                //clear form
                postForm.trigger('reset');
                $('#form_parent').val('');
                $('.reply-block').hide();

                //change captcha
                $('img.captcha').trigger('click');

                //create element
                createNewEntry(entry);
                if (entry['parent']) {
                    topScroll = $('#' + entry['parent'])
                        .find('.sub-entries .row:last')
                        .offset()['top'] - 100
                }
                scrollTo(topScroll);
            }
            postForm.fadeTo('slow', 1);
        }
    };
    postForm.submit(function(e) {
        e.preventDefault();
        postForm.ajaxSubmit(options);
    });

    $(window).scroll(function() {
        if ($(this).scrollTop() > 100) {
            $('.scrollUp').fadeIn();
        } else {
            $('.scrollUp').fadeOut();
        }
    });

    $('.scrollUp').click(function() {
        scrollTo(0);
        return false;
    });

    /**
     * Smoothly Scroll to specified position
     * @param y
     */
    var scrollTo = function(y) {
        $('html, body').animate({
            scrollTop: y
        }, 800);
    };

    /**
     * Create Entry
     * @param properties
     */
    var createNewEntry = function(properties) {
        var prototypeName = properties['parent'] ? 'subentry-prototype' : 'prototype';
        var prototype = $($('#post').data(prototypeName));

        for (var property in properties) {
            var element = prototype.find('.' + property);
            switch (property) {
                case 'message':
                    element.html(properties[property].replace(/\n/g,"<br>"));
                    break;
                case 'image':
                    if (properties[property]) {
                        element.attr('src', properties[property]);
                    }
                    break;
                case 'email':
                    element.attr('href', 'mailto:' + properties[property]);
                    //deliberate fallthrough
                default:
                    element.html(properties[property]);
            }
        }
        backLightEntry(prototype);
        if (properties['parent']) {
            var subEntries = $('#' + properties['parent']).find('.sub-entries');
            var subEntriesCount = parseInt(subEntries.find('.entries-count').html());

            //increase sub messages count
            subEntries.find('.entries-count').html(++subEntriesCount);

            prototype.prepend('<hr />');
            subEntries
                .removeClass('hide')
                .append(prototype);
        } else {
            var entriesCountBlock = $('#entries-count');
            prototype.attr('id', properties['id']);
            prototype.find('.scroll')
                .attr('data-id', properties['id'])
                .attr('data-name', properties['name']);

            //increase sub messages count
            entriesCountBlock.html(parseInt(entriesCountBlock.html()) + 1);

            $('#entries').prepend(prototype);
        }
    };

    /**
     * Highlight entry
     * @param entryBlock
     */
    var backLightEntry = function(entryBlock) {
        entryBlock.css('background', 'rgb(255, 255, 196)');
        setTimeout(function () {
            entryBlock.css('background', '');
        }, 1000);
    };
});