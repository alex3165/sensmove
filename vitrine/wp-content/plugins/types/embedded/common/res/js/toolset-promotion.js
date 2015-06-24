var Toolset_Promotion = Toolset_Promotion  || {};

Toolset_Promotion = function($){
    var self = this;

    self.init = function(){
        self.toolset_open_promotional_message();
    };

    self.toolset_open_promotional_message = function(){
        var $el = $('.js-open-promotional-message')
            , template = $('#js-buy-toolset-embedded-message').html()
            , $container = $('#js-buy-toolset-embedded-message-wrap');

        $container.html( _.template( template ) );

        $(document).on('click', $el.selector, function(event){
            event.preventDefault();
            $.colorbox({
                href: $container.selector,
                inline: true,
                open: true,
                closeButton: false,
                fixed: true,
                top: false,
                width:'554px',
                onComplete: function() {

                },
                onCleanup: function() {

                },
                opacity: .2
            });
        })
        $('.js-close-promotional-message').on('click', function(){
            $.colorbox.close();
        });
    };

    self.init();

};

;(function($){
    var toolset_promotion_message = new Toolset_Promotion($);
}(jQuery));
