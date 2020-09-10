/*
* всплывающие подсказки с фиксированным позиционированием на экране
* */

(function( $ ) {
    var hintInit  = false;
    var hintTimer = false;

    $.fn.hint = function(params) {

        if (params === undefined) {
            params = {
                title : 'title',
                text  : 'text',
                delay : 3000
            };
        }

        hint = "<div class='jq-hint' id='jq-hint'>" +
            "<span><b>"+ params.title +"</b></span><br>" +
            "<span>"+ params.text +"</span>" +
            "</div>";

        hint = $(hint);

        hint.css({
            'top'  : this.offset().top  + this.height() + 15 + 'px',
            'left' : this.offset().left + this.width() - this.width() / 1.2 + 'px'
        });

        if(!hintInit) {
            hintInit = hint;
        } else {
            $('#jq-hint').remove();
            clearInterval(hintTimer);
        }

        $('html body').append(hint);

        hintTimer = setTimeout(function() {
            hint.remove();
        }, params.delay);
    };

})(jQuery);