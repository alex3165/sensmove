(function($){

    $.fn.types_modal_box = function(prop){

        // Default parameters

        var options = $.extend({
            height : 364,
            width : 525
        },prop);

        return this.click(function(e){
            add_block_page();
            add_popup_box();
            add_styles();

            $('.types_modal_box').fadeIn();
        });

        function add_styles(){
            $('.types_modal_box').css({
                position: "absolute",
                display: "none",
                height: options.height.toString() + "px",
                width: options.width.toString() + "px",
                background: "#fff none no-repeat 0 0",
                zIndex: 151,
                border: "1px solid #888",
                boxShadow: "7px 7px 20px 0px rgba(50, 50, 50, 0.75)",
            });
            /*Block page overlay*/
            var pageHeight = $(document).height();
            var pageWidth = $(window).width();

            $('.types_block_page').css({
                position: "absolute",
                top: 0,
                left: 0,
                backgroundColor: "transparent",
                height: pageHeight,
                width: pageWidth,
                zIndex: 101
            });

            $('.types_modal_box .message').css({
                color: "#f05a28",
                fontFamily: "'Open Sans', Helvetica, Arial, sans-serif",
                fontSize: "25px",
                padding: "0 10px",
                textAlign: "center"
            });
            $('.types_modal_box .message span').css({
                background: "transparent url("+types_modal.spinner+") no-repeat 0 50%",
                paddingLeft: "30px",
                lineHeight: "105px"
            });
        }

        function add_block_page(){
            var block_page = $('<div class="types_block_page"></div>');
            $(block_page).appendTo('body');
        }

        function add_popup_box(){
            var marginLeft, height, paddingTop, width;
            var header = types_modal.header;

            if ( !header ) {
                return;
            }

            var html = '<div class="types_modal_box '+types_modal.class+'">';
            html += '<div class="message"><span>'+types_modal.message+'</span></div>';
            if ( 'endabled' == types_modal.state ) {
                html += '<div class="header"><div>';
                if ( types_modal.question ) {
                    html += '<span class="question">';
                    html += types_modal.question;
                    html += '</span>';
                }
                html += '<p>'+header+'</p></div></div>';
            } else {
                options.height = 106;
            }
            html += '</div>';
            var pop_up = $(html);

            pop_up.appendTo('.types_block_page');
            $('#post-body .wpcf-loading').detach();

            pop_up.css({
                top: ($('body').scrollTop() + $('body').height()/2 - options.height/2).toString()+"px",
                left: ($('body').width()/2 - options.width/2).toString()+"px",
            });
            $('.header', pop_up).css({
                height: "259px",
                textAlign: "center",
                color: "#fff",
                fontSize:"15px",
                backgroundImage: 'url('+types_modal.image+'?v=2)',
                backgroundRepeat: "no-repeat",
            });
            /**
             * header div
             */
            marginLeft = "290px";
            width = "220px";
            paddingTop = "50px";
            height = "150px";
            switch(types_modal.class) {
                case 'cred':
                    paddingTop = "77px";
                    marginLeft = "260px";
                    width = "250px";
                    height = "100px";
                    break;
                case 'access':
                    marginLeft = "270px";
                    width = "250px";
                    paddingTop = "25px";
                    height = "120px";
                    break;
            }
            $('.header div', pop_up).css({
                float: "left",
                height: height,
                marginLeft: marginLeft,
                paddingTop: paddingTop,
                textAlign: "left",
                width: width

            });
            /**
             * header p
             */
            $('.header p', pop_up).css({
                fontFamily: "'Open Sans', Helvetica, Arial, sans-serif",
                fontSize: "18px",
                lineHeight: "1.2em",
                margin: 0
            });
            $('.header .question', pop_up).css({
                display: "block",
                fontSize: "14px",
                marginBottom: "5px"
            });
        }

        return this;
    };

})(jQuery);
