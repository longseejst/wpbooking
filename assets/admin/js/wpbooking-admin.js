/**
 * Created by Dungdt on 3/15/2016.
 */
jQuery(document).ready(function( $ ){
    /**
     * Condition tags
     * @type {string}
     */
    var condition_object='select, input[type="radio"]:checked, input[type="text"], input[type="hidden"], input.ot-numeric-slider-hidden-input,input[type="checkbox"]';
    // condition function to show and hide sections
    $('.wpbooking-form-group').on( 'change.conditionals', condition_object, function(e) {
        run_condition_engine();
    });
    run_condition_engine();
    function run_condition_engine(){
        $('.wpbooking-condition[data-condition]').each(function() {
            
            var passed;
            var conditions = get_match_condition( $( this ).data( 'condition' ) );
            var operator = ( $( this ).data( 'operator' ) || 'and' ).toLowerCase();

            $.each( conditions, function( index, condition ) {

                var target   = $( '[name='+ condition.check+']' );

                var targetEl = !! target.length && target.first();

                if ( ! target.length || ( ! targetEl.length && condition.value.toString() != '' ) ) {
                    return;
                }



                var v1 = targetEl.length ? targetEl.val().toString() : '';
                var v2 = condition.value.toString();
                var result;

                if(targetEl.length && targetEl.attr('type')=='radio'){
                    v1 = $( '[name='+ condition.check+']:checked').val();
                }
                if(targetEl.length && targetEl.attr('type')=='checkbox'){
                    v1=targetEl.is(':checked')?v1:'';
                }

                switch ( condition.rule ) {
                    case 'less_than':
                        result = ( parseInt( v1 ) < parseInt( v2 ) );
                        break;
                    case 'less_than_or_equal_to':
                        result = ( parseInt( v1 ) <= parseInt( v2 ) );
                        break;
                    case 'greater_than':
                        result = ( parseInt( v1 ) > parseInt( v2 ) );
                        break;
                    case 'greater_than_or_equal_to':
                        result = ( parseInt( v1 ) >= parseInt( v2 ) );
                        break;
                    case 'contains':
                        result = ( v1.indexOf(v2) !== -1 ? true : false );
                        break;
                    case 'is':
                        result = ( v1 == v2 );
                        break;
                    case 'not':
                        result = ( v1 != v2 );
                        break;
                }

                if ( 'undefined' == typeof passed ) {
                    passed = result;
                }

                switch ( operator ) {
                    case 'or':
                        passed = ( passed || result );
                        break;
                    case 'and':
                    default:
                        passed = ( passed && result );
                        break;
                }

            });

            if ( passed ) {
                $(this).show();
            } else {
                $(this).hide();
            }

            delete passed;

        });
    }

    function get_match_condition(condition){
        var match;
        var regex = /(.+?):(is|not|contains|less_than|less_than_or_equal_to|greater_than|greater_than_or_equal_to)\((.*?)\),?/g;
        var conditions = [];

        while( match = regex.exec( condition ) ) {
            conditions.push({
                'check': match[1],
                'rule':  match[2],
                'value': match[3] || ''
            });
        }

        return conditions;
    }
    // Please do not edit condition section if you don't understand what it is



    ///////////////////////////////////
    /////// MEDIA GALLERY /////////////
    ///////////////////////////////////
    jQuery(document).ready(function($){
        $("body").on('click','.btn_remove_demo_gallery',function(){
            if(confirm(wpbooking_params.delete_gallery) == true) {
                var container = $(this).parent();
                container.find('.fg_metadata').val('');
                container.find('.demo-image-gallery').hide();
                $(this).closest('.wpbooking-gallery').addClass('wpbooking-no-gallery');
                $(this).hide();
            }
        });

        var file_frame;
        $('body').on('click','.btn_upload_gallery',function (event) {
            var container = $(this).parent();
            var cls = $(this).closest('.wpbooking-gallery');

            event.preventDefault();
            // If the media frame already exists, reopen it.
            if ( file_frame ) {
                file_frame.open();
                return;
            }
            // Create the media frame.
            file_frame = wp.media.frame = wp.media({
                frame: "post",
                state: "gallery",
                library : { type : 'image'},
                // button: {text: "Edit Image Order"},
                multiple: true
            });
            file_frame.on('open', function() {
                var selection = file_frame.state().get('selection');
                var ids = container.find('.fg_metadata').val();
                if (ids) {
                    idsArray = ids.split(',');
                    idsArray.forEach(function(id) {
                        attachment = wp.media.attachment(id);
                        attachment.fetch();
                        selection.add( attachment ? [ attachment ] : [] );
                    });
                }
            });
            // When an image is selected, run a callback.
            file_frame.on('update', function() {
                var imageIDArray = [];
                var imageHTML = '';
                var metadataString = '';
                images = file_frame.state().get('library');

                images.each(function(attachment) {
                    imageIDArray.push(attachment.attributes.id);
                    imageHTML += '<img class="demo-image-gallery settings-demo-gallery" src="'+attachment.attributes.url+'">';
                });

                metadataString = imageIDArray.join(",");
                if (metadataString) {
                    $('.fg_metadata',container).val(metadataString);
                    $('.featuredgallerydiv',container).html(imageHTML).show();
                    cls.removeClass('wpbooking-no-gallery');
                    $('.btn_remove_demo_gallery').show();
                }
            });
            file_frame.open();
        });
    });
    ///////////////////////////////////
    /////// MEDIA GALLERY HOTEL////////
    ///////////////////////////////////
    var modal = (function(){
        var
            method = {},
            $overlay,
            $modal,
            $content,
            $content_image,
            $content_rooms,
            $close,
            $title;

        // Center the modal in the viewport
        method.center = function () {
            var top, left;

            top = Math.max($(window).height() - $modal.outerHeight(), 0) / 2;
            left = Math.max($(window).width() - $modal.outerWidth(), 0) / 2;

            $modal.css({
                top:top + $(window).scrollTop(),
                left:left + $(window).scrollLeft()
            });
        };

        // Open the modal
        method.open = function (settings) {
            $title.empty().append(settings.title);
            $content_image.children().empty().append(settings.content_image);
            $content_rooms.find('.content_rooms').empty().append(settings.content_rooms);
            $content.attr('data-id',settings.img_id);
            $content.find('.del-image-perman').children().empty().append(settings.title_delete);
            $content.find('.del-image-perman').children().attr('data-id',settings.img_id);

            $modal.css({
                width: settings.width || 'auto',
                height: settings.height || 'auto'
            });

            setTimeout(function() {
                method.center();
                $(window).bind('resize.modal', method.center);
                $modal.show();
                $overlay.show();
            },100);
        };

        // Close the modal
        method.close = function () {
            $modal.hide();
            $overlay.hide();
            $content_rooms.find('.content_rooms').empty();
            $content.find('.del-image-perman').children().empty();
            $(window).unbind('resize.modal');
        };

        // Generate the HTML and add it to the document
        $overlay = $('<div id="wb-modal-overlay"></div>');
        $modal = $('<div class="modal_image"></div>');
        $content = $('<div class="wb-modal content_detail"></div>');
        $content_image = $('<div class="content_left"><div class="content_image"></div></div>');
        $content_rooms = $('<div class="content_right" data-id=""><div class="content_rooms"></div><div class="del-image-perman"><a class="st_delete_attachment" href="javascript:void(0)"></a></div></div>');
        $close = $('<a class="media-modal-close close_modal" href="#"><span class="media-modal-icon"></span></a>');
        $title = $('<h3 class="title_popup"></h3>');

        $modal.hide();
        $overlay.hide();
        $content.append($content_image ,$content_rooms);
        $modal.append($title ,$content, $close);

        $(document).ready(function(){
            $('body').append($overlay, $modal);
        });

        return method;
    }());

    jQuery(document).ready(function($){
        //if empty will remove all data
        if($('.featuredgallerydiv').html() == ''){
            $('.wp_gallery_hotel').val('');
            $('.wb_hotel_gallery_data').val('');
            $('.btn_remove_gallery_hotel').hide();
        }

        $("body").on('click','.close_modal',function(e){
            e.preventDefault();
            modal.close();
        });

        // Save
        $(document).on('change','.wp_room_hotel',function(){
            $(this).each(function(){
                var old=$('.wb_hotel_gallery_data').val();
                var selected_room=$('.content_rooms .wp_room_hotel');
                var media_id=$(this).data('media-id');

                if(!old) old='{}';

                try{
                    var json=JSON.parse(old);

                    selected_room.each(function(){
                        if(json[$(this).val()] === undefined) json[$(this).val()] = new Array();
                        if($(this).is(":checked") && $.inArray(media_id,json[$(this).val()]) == -1){
                            json[$(this).val()].push(media_id);
                        }
                        if(!$(this).is(":checked") && $.inArray(media_id,json[$(this).val()]) > -1){
                            json[$(this).val()].splice(json[$(this).val()].indexOf(media_id),1);
                        }
                    });

                    $('.wb_hotel_gallery_data').val(JSON.stringify(json));

                }catch(e){
                    console.log(e);
                }
            });
        });

        $("body").on('click','.btn_remove_gallery_hotel',function(e){
            var container = $(this).parent().parent();
            var wn = confirm(wpbooking_params.delete_gallery);
            if(wn == true) {
                container.find('.wp_gallery_hotel').val('');
                container.find('.wb_hotel_gallery_data').val('');
                container.find('.gallery-item').remove();
                $(this).closest('.wb-form-group-gallery').addClass('wpbooking-no-gallery');
                $(this).hide();
            }
        });

        $("body").on('click','.st_delete_attachment',function(){
            var wn = confirm(wpbooking_params.delete_permanently_image);
            if(wn == true) {
                var img_id = $(this).attr('data-id');
                $.ajax({
                    url: wpbooking_params.ajax_url,
                    dataType: 'json',
                    type: 'post',
                    data: {
                        action: 'wpbooking_delete_attachment',
                        img_id: img_id,
                    },
                    success: function (res) {
                        var images = $('#wp_gallery_hotel').attr('value');
                        var img_arr = images.split(',');
                        img_arr = jQuery.grep(img_arr, function (a) {
                            return a != img_id;
                        });
                        $('.wp_gallery_hotel').val(img_arr.join(','));
                        if(img_arr.length == 0){
                            $('.btn_remove_gallery_hotel').hide();
                            $('.wb-form-group-gallery').addClass('wpbooking-no-gallery');
                        }

                        $('.featuredgallerydiv').find('.gallery-item').each(function () {
                            var id = $(this).find('.gallery-item-remove').data('id');
                            if (id == img_id) {
                                $(this).remove();
                            }
                        });
                        var old=$('.wb_hotel_gallery_data').val();
                        if(!old) old='{}';
                        var json=JSON.parse(old);
                        $.each(json, function(key, val){
                            if($.inArray(parseInt(img_id),val) > -1){
                                json[key].splice(val.indexOf(parseInt(img_id)),1);
                            }
                        });
                        $('.wb_hotel_gallery_data').val(JSON.stringify(json));

                        modal.close();
                    },
                    error: function (e) {
                        alert('Can not get the order data. Lost connect with your sever');
                        console.log(e);
                    }
                });
            }
        });

        $("body").on('click','.gallery-item-btn.gallery-item-remove',function(){
                var t = $(this);
            var cls = $(this).closest('.wb-form-group-gallery');
                var domain = t.closest('.featuredgallerydiv').data('domain');
                var domain_arr = domain.split(',');
                var wn = confirm(domain_arr[0]);
                if(wn == true) {
                    var images = $('#wp_gallery_hotel').attr('value');
                    var img_arr = images.split(',');
                    img_arr = jQuery.grep(img_arr, function( a ) {
                        return a != t.data('id');
                    });
                    t.closest('.form-group').find('.wp_gallery_hotel').val(img_arr.join(','));
                    var old=$('.wb_hotel_gallery_data').val();
                    if(!old) old='{}';
                    var json=JSON.parse(old);
                    $.each(json, function(key, val){
                        if($.inArray( t.data('id'),val) > -1){
                            json[key].splice($.inArray( t.data('id'),val),1);
                        }
                    });

                    var str_json =JSON.stringify(json);
                    $('.wb_hotel_gallery_data').val(str_json);
                    if(Object.keys(json).length == 0){
                        $('.wb_hotel_gallery_data').val('');
                    }
                    $(this).parent().parent().remove();
                    if($('#wp_gallery_hotel').val() == ''){
                        cls.addClass('wpbooking-no-gallery');
                        $('.btn_remove_gallery_hotel').hide();
                    }
                }
        });

        $("body").on('click','.gallery-item-btn.gallery-item-edit',function(){
            var t = $(this);
            var domain = $(this).closest('.featuredgallerydiv').data('domain');
            var domain_arr = domain.split(',');
            var title = domain_arr[1];
            var url = $(this).data('url');
            var data_room = $(this).closest('.featuredgallerydiv').attr('data-room');
            var list_room = '';
            var old=$('.wb_hotel_gallery_data').val();
            if(!old) old='{}';
            var json = JSON.parse(old);
            $.each(JSON.parse(data_room),function(key, value){
                var checked = '';
                if(json[value.ID.toString()] != undefined) {
                    if ($.inArray(t.data('id'), json[value.ID.toString()]) > -1) {
                        checked = 'checked';
                    }
                }
                list_room += "<div class='full-width mb10'><label><input type='checkbox' " + checked + " class='wp_room_hotel' data-media-id='"+ t.data('id')+"' name='wp_room_hotel' value='"+value.ID+"'/> "+value.post_title+"</label></div>";
            });
            modal.open({
                width:1200,
                title : title,
                content_image: "<img data-id='' src='"+url+"' />",
                content_rooms: "<h3 class='title_list_room'>"+domain_arr[2]+"</h3>"+list_room,
                title_delete : domain_arr[3],
                img_id :$(this).data('id')
            });
        });

        var file_frame;

        $('body').on('click','.btn_upload_gallery_hotel',function (event) {
            var container = $(this).parent().parent();
            var cls = $(this).closest('.wb-form-group-gallery');
            event.preventDefault();
            // If the media frame already exists, reopen it.
            if ( file_frame ) {
                file_frame.open();
                return;
            }
            // Create the media frame.

            file_frame = wp.media.frame = wp.media({
                frame: "post",
                state: "gallery",
                library : { type : 'image'},
                // button: {text: "Edit Image Order"},
                multiple: true
            });
            file_frame.on('open', function() {
                var selection = file_frame.state().get('selection');
                var ids = container.find('.wp_gallery_hotel').val();
                if (ids) {
                    idsArray = ids.split(',');
                    idsArray.forEach(function(id) {
                        attachment = wp.media.attachment(id);
                        attachment.fetch();
                        selection.add( attachment ? [ attachment ] : [] );
                    });
                }
            });
            // When an image is selected, run a callback.
            file_frame.on('update', function() {
                var imageIDArray = [];
                var imageHTML = '';
                var metadataString = '';
                images = file_frame.state().get('library');
                images.each(function(attachment) {
                    imageIDArray.push(attachment.attributes.id);
                    //console.log(attachment.attributes);
                    imageHTML += '<div class="gallery-item">';
                    imageHTML += '<img class="demo-image-gallery settings-demo-image-gallery" src="'+attachment.attributes.sizes.thumbnail.url+'">';
                    imageHTML += '<div class="gallery-item-control text-center"><a href="javascript:void(0)" class="gallery-item-btn gallery-item-edit" data-url="'+attachment.attributes.sizes.full.url+'" data-id="'+attachment.attributes.id+'"><i class="fa fa-pencil-square-o"></i></a><a href="javascript:void(0)" class="gallery-item-btn gallery-item-remove" data-id="'+attachment.attributes.id+'"><i class="fa fa-trash"></i></a></div>';
                    imageHTML += '</div>';
                });

                metadataString = imageIDArray.join(",");
                if (metadataString) {
                    $('.wp_gallery_hotel',container).val(metadataString);
                    $('.featuredgallerydiv',container).html(imageHTML).show();
                    cls.removeClass('wpbooking-no-gallery');
                    $('.btn_remove_gallery_hotel').show();
                }
            });
            file_frame.open();
        });
    });
    ///////////////////////////////////
    /////// MEDIA IMAGE ///////////////
    ///////////////////////////////////
    jQuery(document).ready(function($){
        //$(".btn_remove_demo_image").click(function(){
        $(document).on('click','.btn_remove_demo_image',function(){
            var container = $(this).parent();
            container.find('.demo-url-image').val('');
            container.find('.demo-image').hide();
        });
        $('.btn_upload_media').each(function(){
            $(this).click(function(e){
                var container = $(this).parent();
                var insertImage = wp.media.controller.Library.extend({
                    defaults :  _.defaults({
                        id:        'insert-image',
                        title:      'Insert Image Url',
                        allowLocalEdits: true,
                        displaySettings: true,
                        displayUserSettings: true,
                        type : 'image'
                    }, wp.media.controller.Library.prototype.defaults )
                });
                var frame = wp.media({
                    button : { text : 'Select' },
                    state : 'insert-image',
                    states : [
                        new insertImage()
                    ]
                });
                frame.on( 'select',function() {
                    var state = frame.state('insert-image');
                    var selection = state.get('selection');
                    if ( ! selection ) return;
                    selection.each(function(attachment) {
                        console.log(attachment);
                        container.find('#st_url_media').val(attachment.attributes.url);
                        container.find('#demo_img').attr("src",attachment.attributes.url).show();
                    });
                });
                frame.on('open',function() {
                    var selection = frame.state('insert-image').get('selection');
                    selection.each(function(image) {
                        var attachment = wp.media.attachment( image.attributes.id );
                        attachment.fetch();
                        selection.remove( attachment ? [ attachment ] : [] );
                    });
                });
                frame.open();
            });
        })
    });
    ///////////////////////////////////
    /////// IMAGE THUMB ///////////////
    ///////////////////////////////////
    $(document).on('keyup', '.wpbooking_image_thumb_width', function(event) {
        var container = $(this).parent();
        _save_data_image_thumb(container);
    });
    $(document).on('keyup', '.wpbooking_image_thumb_height', function(event) {
        var container = $(this).parent();
        _save_data_image_thumb(container);
    });
    $(document).on('change', '.wpbooking_image_thumb_crop', function(event) {
        var container = $(this).closest('td');
        _save_data_image_thumb(container);
    });
    function _save_data_image_thumb(container){
        var height = container.find('.wpbooking_image_thumb_height').val();
        var width = container.find('.wpbooking_image_thumb_width').val();
        if ( container.find('.wpbooking_image_thumb_crop').is(":checked"))
        {
            var crop = 'on';
        }else{
            var crop = 'off';
        }
        console.log(crop);
        var value = width+","+height+","+crop;
        console.log(value);
        container.find('.data_value').val(value);
    }

    ///////////////////////////////////
    /////// Meta Box ///////////////
    ///////////////////////////////////
    var resize;
    $(window).resize(function(event) {
        clearTimeout( resize );

        resize = setTimeout(function(){
            //if( $(window).width() < 1024 ){
            //    if( $( ".st-metabox-tabs" ).length ){
            //        $( ".st-metabox-tabs" ).tabs().removeClass( "ui-tabs-vertical ui-helper-clearfix" );
            //        $( ".st-metabox-tabs li" ).addClass( "ui-corner-top" ).removeClass( "ui-corner-left" );
            //    }
            //}else{
            //    if( $( ".st-metabox-tabs" ).length ){
            //        $( ".st-metabox-tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
            //        $( ".st-metabox-tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
            //    }
            //}
        }, 500);
    }).resize();

    // move field to hndle tags
    $( '.wpbooking-hndle-tag-input').each(function(){
        var me=$(this);
        var hndle=me.closest('.postbox').find('.hndle');
        hndle.find('span').append(me.html());
        hndle.unbind( 'click.postboxes' );
        hndle.click( function( event ) {
            if ( $( event.target ).filter( 'input, option, label, select' ).length ) {
                return;
            }
            me.closest('.postbox').toggleClass( 'closed' );
        });
        me.detach();
    });


    ///////////////////////////////////
    /////// LIST ITEM /////////////////
    ///////////////////////////////////
    $( ".data_content_list_item" ).each(function(){
        $(this).sortable({
            handle: ".dashicons"
        });
        //dashicons
    });
    //$(".btn_add_new_list_item").click(function () {
    $(document).on('click','.btn_add_new_list_item',function(){
        var container = $(this).parent();
        var content_html = container.find(".content_list_item_hide").html();
        var number_list = container.find('.wpbooking_number_last_list_item').val();
        content_html = content_html.replace(/__number_list__/g, number_list);
        container.find('.data_content_list_item').append(content_html);
        container.find('.wpbooking-setting-setting-body').slideUp('fast');
        container.find('.number_list_' + number_list + ' .wpbooking-setting-setting-body').slideDown('fast');
        number_list = Number(number_list) + 1;
        container.find('.wpbooking_number_last_list_item').val(number_list);
    });
    $(document).on('click', '.btn_list_item_del', function (event) {
        event.preventDefault();
        var confirm_delete=confirm('Are you want to delete it?');
        if(confirm_delete) {
            var container = $(this).parent().parent().parent();
            container.remove();
        }
    });
    $(document).on('click', '.btn_list_item_edit', function (event) {
        var container_full = $(this).parent().parent().parent().parent();
        var container = $(this).parent().parent().parent();
        container_full.find('.wpbooking-setting-setting-body').slideUp('fast');
        $check = container.find('.wpbooking-setting-setting-body').css('display');
        if ($check == "none") {
            container.find('.wpbooking-setting-setting-body').slideDown('fast');
        } else {
            container.find('.wpbooking-setting-setting-body').slideUp('fast');
        }
    });
    $(document).on('click', '.list_item_title', function (event) {
        $(this).keyup(function () {
            var $value = $(this).val();
            var container = $(this).parent().parent().parent().parent().parent().parent().parent();
            console.log(container);
            container.find('.list-title').html($value);
        });
    });

    ///////////////////////////
    //////  Gmap    //////////
    ///////////////////////////
    
    function load_gmap(){
        if(typeof google=='undefined') return;

        if( $('.wpbooking-gmap-wrapper').length ){
            $('.wpbooking-gmap-wrapper').each(function(index, el) {
                console.log('map load ...');
                var t = $(this);
                var gmap = $('.gmap-content', t);
                var map_lat = parseFloat( $('input[name="map_lat"]', t).val() );
                var map_long = parseFloat( $('input[name="map_long"]', t).val() );

                var map_zoom = parseInt( $('input[name="map_zoom"]', t).val() );

                var bt_ot_searchbox = $('input.gmap-search', t);

                var current_marker;
                var map_options={
                    options:{
                        mapTypeId: google.maps.MapTypeId.ROADMAP,
                        mapTypeControl: true,
                        mapTypeControlOptions: {
                            style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
                        },
                        navigationControl: true,
                        scrollwheel: true,
                    },
                    //events:{
                    //    click: function(marker, event, context){
                    //        $('input[name="map_lat"]', t).val( marker.latLng.lat() );
                    //        $('input[name="map_long"]', t).val( marker.latLng.lng() );
                    //        $('input[name="map_zoom"]', t).val( marker.zoom );
                    //
                    //        $(this).gmap3({
                    //            clear: {
                    //                name:["marker"],
                    //                last: true
                    //            }
                    //        });
                    //        $(this).gmap3({
                    //            marker:{
                    //                values:[
                    //                    {latLng:marker.latLng },
                    //                ],
                    //                options:{
                    //                    draggable: false
                    //                },
                    //            }
                    //        });
                    //    }
                    //}
                };
                if(map_lat && map_long){
                    map_options.options.center=[map_lat, map_long];
                    map_options.marker={
                                        values:[
                                            {latLng:new google.maps.LatLng({lat: map_lat, lng: map_long}) },
                                        ],
                                        options:{
                                            draggable: false
                                        },
                                    };
                }
                if(map_zoom){
                    map_options.options.zoom=map_zoom;
                }

                console.log(map_options);
                gmap.gmap3({
                    map:map_options
                });

                var gmap_obj = gmap.gmap3('get');

                // Map Click
                gmap_obj.addListener('click', function(e) {

                    $('input[name="map_lat"]', t).val( e.latLng.lat() );
                    $('input[name="map_long"]', t).val( e.latLng.lng() );

                    gmap.gmap3({
                        clear: {
                            name:["marker"],
                            last: true
                        }
                    });
                    gmap.gmap3({
                        marker:{
                            values:[
                                {latLng:e.latLng },
                            ],
                            options:{
                                draggable: false
                            },
                        }
                    });
                });

                if(!map_lat || !map_long){
                     //Try to get current location
                    /**
                     * Hide it since 21-10-2016 by Dungdt
                     */
                    // if (navigator.geolocation) {
                    //     navigator.geolocation.getCurrentPosition(function(showPosition){
                    //         var gmap_obj = gmap.gmap3('get');
                    //         map_lat=showPosition.coords.latitude;
                    //         map_long=showPosition.coords.longitude;
                    //         gmap_obj.setCenter(new google.maps.LatLng(map_lat,map_long));
                    //         gmap_obj.setZoom(11);
                    //         $('input[name="map_lat"]', t).val( map_lat );
                    //         $('input[name="map_long"]', t).val( map_long );
                    //         $('input[name="map_zoom"]', t).val( 11);
                    //     });
                    //
                    // }
                }


                var geocoder = new google.maps.Geocoder;

                var map_type = "roadmap";

                if( bt_ot_searchbox.length ){
                    var searchBox = new google.maps.places.SearchBox( bt_ot_searchbox[0] );

                    google.maps.event.addListener(searchBox, 'places_changed', function() {
                        var places = searchBox.getPlaces();
                        if (places.length == 0) {
                            return;
                        }

                        // For each place, get the icon, place name, and location.
                        var bounds = new google.maps.LatLngBounds();
                        for (var i = 0, place; place = places[i]; i++) {

                            bounds.extend(place.geometry.location);

                            if(i == 0){

                                gmap.gmap3({
                                    clear: {
                                      name:["marker"],
                                      last: true
                                    }
                                });
                                gmap.gmap3({
                                    marker:{
                                        values:[
                                          {latLng: place.geometry.location },
                                        ],
                                        options:{
                                          draggable: false
                                        },
                                    }
                                });

                                $('input[name="map_lat"]', t).val( place.geometry.location.lat() );
                                $('input[name="map_long"]', t).val( place.geometry.location.lng() );
                                $('input[name="map_zoom"]', t).val( gmap_obj.getZoom() );

                            }
                        }

                        gmap_obj.fitBounds(bounds);

                    });

                }

                google.maps.event.addListener(gmap_obj, "zoom_changed", function(event) {
                    $('input[name="map_zoom"]', t).val( gmap_obj.getZoom() );
                });

                $(window).resize(function(){
                    google.maps.event.trigger(gmap_obj, 'resize');
                });
            });
        }
    }


    $('.wpbooking_extra_service-checklist,.wpbooking_location-checklist').prev().prev().hide();



    if( $( ".st-metabox-tabs" ).length ){
        $( ".st-metabox-tabs" ).tabs({
            activate: function( event, ui ) {
                if( room_calendar ){
                    $('.wpbooking-calendar-wrapper .calendar-room').fullCalendar( 'today' );
                }
            }
        });
    }

    /////////////////////////////////
    /////// List item //////////////
    ///////////////////////////////
    $( ".wpbooking-list-item-wrapper .wpbooking-list" ).sortable({
        cursor: "move"
    });

    //$('.wpbooking-add-item').click(function(event) {
    $(document).on('click','.wpbooking-add-item',function(){
        /* Act on the event */
        if( $('#wpbooking-list-item-draft').length ){
            var content = $('#wpbooking-list-item-draft').html();

            var parent = $(this).closest('.wpbooking-list-item-wrapper');

            $('.wpbooking-list', parent).append( content );
            $( ".wpbooking-list-item-wrapper .wpbooking-list" ).sortable({
                cursor: "move"
            });
            $('.icp-auto').iconpicker();

        }
        return false;
    });

    $('.wpbooking-list-item-wrapper').on('click', '.btn_list_item_edit', function(event) {
        var parent = $(this).closest('.list-item-head');
        parent.next().stop(true, true).toggleClass('hidden');

        var list_item=$(this).closest('.wpbooking-list-item');
        if(parent.next().hasClass('hidden')){
            list_item.removeClass('active');
        }else{
            list_item.addClass('active');
        }

        //if(list_item.hasClass('active')){
        //    list_item.removeClass('active');
        //}else{
        //    wrap.find('.wpbooking-list-item').removeClass('active');
        //    list_item.addClass('active');
        //}

        event.preventDefault();
        /* Act on the event */
    });

    $('.wpbooking-list-item-wrapper').on('click', '.btn_list_item_del', function(event) {
        var parent = $(this).closest('.wpbooking-list-item');
        parent.remove();

        event.preventDefault();
        /* Act on the event */
    });

    

    $('.wpbooking-list-item-wrapper').on('keyup', '.input-title', function(event) {
        var parent = $(this).closest('.wpbooking-list-item');
        var val = $(this).val();
        $('.item-title', parent).text( val );

        event.preventDefault();
        /* Act on the event */
    });

    /////////////////////////////
    ////////// Location ////////
    ////////////////////////////

    if( $('.wpbooking-select-loction').length ){
        $('.wpbooking-select-loction').each(function(index, el) {
            var parent = $(this);
            var input = $('input[name="search"]', parent);
            var list = $('.list-location-wrapper', parent);
            var timeout;
            input.keyup(function(event) {
                clearTimeout( timeout );
                var t = $(this);
                timeout = setTimeout(function(){
                    var text = t.val();
                    if( text == ''){
                        $('.item', list).show();
                    }else{
                        $('.item', list).hide();
                        $(".item[data-name*='"+text.toLowerCase()+"']", list).show();
                    }
                    
                }, 500);
            });
        });
    }

    $(window).load(function(){
        $('.ace-editor').each(function(){
            var type=$(this).data('type');
            var input=$(this).next('textarea');
            var editor = ace.edit($(this).attr('id'));
            editor.setTheme("ace/theme/monokai");
            editor.getSession().setMode("ace/mode/css");
            editor.getSession().setValue( input.val() );

            $('#form-settings-admin').submit(function(){
                input.val(editor.getSession().getValue());
            });
        });
    });


    // ALl Booking Calendar
    $('#wpbooking_order_calendar .calendar-wrap').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        //eventLimit: true, // allow "more" link when too many events,
        height:500,
        numberOfMonths: 2,
        events:function(start, end, timezone, callback) {
            var filter=$('.tablenav');
            $.ajax({
                url: wpbooking_params.ajax_url,
                dataType: 'json',
                type:'post',
                data: {
                    action: 'wpbooking_order_calendar',
                    start: start.unix(),
                    end: end.unix(),
                    filter:filter.find('input,select').serialize()
                },
                success: function(doc){
                    if(typeof doc == 'object'){
                        callback(doc);
                    }
                },
                error:function(e){
                    alert('Can not get the order data. Lost connect with your sever');
                    console.log(e);
                }
            });
        },
        eventMouseover: function(event, element, view){
            $('.popover').remove();
            var html = event.tooltipContent;
            $(this).popover({
                content:html,
                placement:'bottom',
                container:'body',
                html:true
            });
            $(this).popover('show');

        },
        eventMouseout:function(){
            //$(this).popover('hide');
        }

    });

    // Accordion
    //$('.wpbooking-metabox-accordion').accordion();
    //$('.wpbooking-accordion-title').click(function(){
    $(document).on('click','.wpbooking-accordion-title',function(){
       if($(this).parent().hasClass('active')){
           $(this).parent().find('.wpbooking-metabox-accordion-content').slideUp('fast');
           $(this).parent().removeClass('active');
       }else{
           var s=$(this).parent().siblings('.wpbooking-metabox-accordion');
           s.find('.wpbooking-metabox-accordion-content').slideUp('fast');
           s.removeClass('active');
           $(this).parent().find('.wpbooking-metabox-accordion-content').slideDown('fast');
           $(this).parent().addClass('active');
            console.log(1);
           $(window).trigger('resize');
       }
    });

    $(document).on('keyup', '.wpbooking_image_thumb_height', function(event) {
        var container = $(this).parent();
        _save_data_image_thumb(container);
    });
    // On-off
    $(document).on('click', '.wpbooking-switch', function(event) {
        $(this).toggleClass("switchOn",function(){

        });
        var checkbox=$(this).closest('.wpbooking-switch-wrap').find('.checkbox');

        if($(this).hasClass('switchOn')){
            checkbox.val('on');
        }else{
            checkbox.val('off');
        }
    });

    //Popover
    $('.wb-help-popover').popover({
        //container:'body',
        template:'<div class="popover wb-help-popover-el" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
    });

    // Next, Prev Button
    //$('.wb-prev-section').click(function(){
    $(document).on('click','.wb-prev-section',function(){
        var h=$('#st_post_metabox').offset().top;
        $('.st-metabox-nav li.ui-state-active').prev().find('a').trigger('click');
        $('html,body').animate({'scrollTop':parseInt(h)-200});
        return false;
    });
    //$('.wb-next-section').click(function(){
    $(document).on('click','.wb-next-section',function(){
        t = $(this);
        var next_a,section;
        next_a=$('.st-metabox-nav li.ui-state-active').next().find('a');
        section=$(this).closest('.st-metabox-tabs-content');
        t.addClass('loading');

        if($(this).hasClass('ajax_saving')){
            saveMetaboxSection(section,$(this),function(){
                if(t.data('action') != 'edit') {
                    next_a.trigger('click');
                    var h = $('#st_post_metabox').offset().top;
                    $('html,body').animate({'scrollTop': parseInt(h) - 200});
                }

                t.removeClass('loading');
            });
        }else{
            if($(this).data('action') != 'edit') {
                next_a.trigger('click');
                var h = $('#st_post_metabox').offset().top;
                $('html,body').animate({'scrollTop': parseInt(h) - 200});
            }
        }


        return false;
    });

    function saveMetaboxSection(section,button,success_callback){

        if(section.hasClass('active')) return;
        section.addClass('loading');

        var data=section.find('input,select,textarea').serialize();

        section.find('.is_error').removeClass('is_error');
        section.find('.is_error_message').remove();

        data+='&action=wpbooking_save_metabox_section';

        $.ajax({
            url:wpbooking_params.ajax_url,
            data:data,
            dataType:'json',
            type:'post',
            success:function(res){
                if(res.status){
                    success_callback(res);
                }

                if(res.message){
                    alert(message);
                }
                if(section.find('input[name=wb_meta_section]').val()=="photo_tab"){
                    wpbooking_reload_image_room(section.find('input,select,textarea'));
                }

                if(typeof res.error_fields!=='undefined')
                {
                    for(var k in res.error_fields){

                        var field=section.find("[name='"+k+"']");
                        if(!field.length){
                            field=section.find('.field-'+k+' .st-metabox-content-wrapper .form-group');
                        }
                        if(!field.length){
                            field=section.find('.field-'+k);
                        }
                        field.addClass('is_error');
                        $('<span class="is_error_message">'+res.error_fields[k]+'</span>').insertAfter(field);
                    }

                    var first_error=section.find('.is_error:first-child');
                    if(first_error.length){
                        var h = section.find(first_error).offset().top;
                        $('html,body').animate({'scrollTop': parseInt(h) - 200});
                    }

                }
                section.removeClass('loading');
            },
            error:function(e){
                alert('Can not save section data, please reload page and try again');
                console.log(e.responseText);
                section.removeClass('loading');
            }
        })
    }

    function wpbooking_reload_image_room(data) {
        console.log(data.name);
        data = data.serialize()
        data+='&action=wpbooking_reload_image_list_room';
        $.ajax({
            url:wpbooking_params.ajax_url,
            data:data,
            dataType:'json',
            type:'post',
            success:function(res){
                if(res){
                    for(var room_id in res) {
                        var image = res[room_id];
                        $('.item-hotel-room-'+room_id).find('.room-image').html(image);
                    }
                }
            },
        })
    }
    // Ajax Create Term
   // $('.wb-btn-add-term').click(function(){
    $(document).on('click','.wb-btn-add-term',function(){

        var parent=$(this).parent();
        var me=$(this);
        var list_terms=$(this).closest('.st-metabox-right').find('.list-terms-checkbox');
        var term_name=parent.find('.term-name').val();
        var tax_name=me.data('tax');
        console.log(term_name);
        if(!term_name) return false;

        parent.addClass('loading');
        $.ajax({
            url:wpbooking_params.ajax_url,
            type:'post',
            dataType:'json',
            data:{
                action:'wpbooking_add_term',
                term_name:term_name,
                taxonomy:tax_name,
                other_data:parent.find('input').serialize()
            },
            success:function(res){
                parent.removeClass('loading');
                if(res.status){
                    if(res.data.term_id && res.data.name){
                        var input_name=me.data('name');

                        // Icon
                        var extra_html='';
                        if(typeof res.extra_fields!='undefined' && typeof res.extra_fields.icon !='undefined'){
                            extra_html+='<span class="icon"><i class="'+res.extra_fields.icon+'"></i></span>';
                        }
                        list_terms.append('<div class="term-checkbox"><label><input type="checkbox" name="'+input_name+'[]" value="'+res.data.term_id+'">'+extra_html+'<span>'+res.data.name+'</span></label></div>')
                    }
                    parent.find('.term-name').val('');
                }
                if(res.message){
                    alert(res.message);
                }
            },
            error:function(e){
                parent.removeClass('loading');
                list_terms.append(e.responseText);
            }
        })
    });


    // Add Extra Services
    //$('.wb-btn-add-extra-service').click(function(){
    $(document).on('click','.wb-btn-add-extra-service',function(){
        var parent=$(this).parent();
        var wrap=$(this).closest('.add-new-extra-service');
        var me=$(this);
        var list_terms=$(this).closest('.st-metabox-right').find('.list-extra-services');
        var term_name=parent.find('.service-name ').val();
        var term_desc=parent.find('.service-desc ').val();
        var max_quantity=parent.find('.max-quantity-sv option:selected').val();
        var service_type=me.data('type');
        var id=me.data('id');
        if(!term_name) return false;

        parent.addClass('loading');
        $.ajax({
            url:wpbooking_params.ajax_url,
            type:'post',
            dataType:'json',
            data:{
                action:'wpbooking_add_extra_service',
                service_name:term_name,
                service_desc:term_desc,
                service_type:service_type
            },
            success:function(res){
                parent.removeClass('loading');
                if(res.status){

                    var input_name=me.data('name');
                    var html=wrap.find('.extra-item-default .extra-item').clone();
                    var count=list_terms.find('.extra-item').length;
                    html.find('.title input').attr('name',id+'['+(count)+'][is_selected]');
                    html.find('.title input').val(term_name);
                    html.find('.service_desc').html(term_desc);
                    html.find('.extra-item-name').html(term_name);
                    html.find('.max-quantity-select option').each(function(){
                        if($(this).val() == max_quantity){
                            $(this).attr('selected','');
                        }
                    });
                    html.find('.money-number input').attr('name',id+'['+(count)+'][money]');
                    html.find('.require-options select').attr('name',id+'['+(count)+'][require]');
                    html.find('.max-quantity .max-quantity-select').attr('name',id+'['+(count)+'][quantity]');

                    list_terms.append(html);

                    parent.find('.service-name').val('');
                    parent.find('.service-desc').val('');
                    parent.find('.max-quantity-sv option[value=""]').attr('selected','');
                }
                if(res.message){
                    alert(res.message);
                }
            },
            error:function(e){
                parent.removeClass('loading');
                list_terms.append(e.responseText);
            }
        })
    });

    // Icon picker
    $('.icp-auto').iconpicker();

    /**
     * I-Check
     */
    $('.wb-icheck').each(function(){
        $(this).iCheck({
            checkboxClass:$(this).data('style'),
            radioClass:$(this).data('style'),
        });
    });

    // Show More Less
    //$('.cart-item-order-form-fields-wrap .show-more-less').click(function(){
    $(document).on('click','.cart-item-order-form-fields-wrap .show-more-less',function(){
        $(this).parent().toggleClass('active');
    })


    // Datepicker Field
    $('.wb-date').datepicker();

    $(window).load(function(){
        // Auto Complete Post Type
        $('.wb-autocomplete').each(function() {
                var me = $(this);
                me.select2({
                    ajax: {
                        url: wpbooking_params.ajax_url,
                        dataType: 'json',
                        type:'post',
                        data: function (params) {
                            return {
                                q: params.term, // search term
                                action: 'wpbooking_autocomplete_post',
                                page: params.page,
                                type:me.data('type')
                            };
                        },
                        processResults: function (data, params) {
                            // parse the results into the format expected by Select2
                            // since we are using custom formatting functions we do not need to
                            // alter the remote JSON data, except to indicate that infinite
                            // scrolling can be used
                            params.page = params.page || 1;

                            return {
                                results: data
                            };
                        },
                        cache: true
                    },
                    minimumInputLength:3,
                    escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
                    templateResult:  function (post) {
                        if(post.loading) return post.text;
                        var markup = "<div class='select2-result-repository clearfix'>" +
                            "<div class='select2-result-repository__avatar'>"+post.thumb+"</div>" +
                            "<div class='select2-result-repository__meta'>" +
                            "<div class='select2-result-repository__title'>" + post.text + "</div>";

                        if (post.address) {
                            markup += "<div class='select2-result-repository__description'>" + post.address + "</div>";
                        }
                        markup+='</div>';

                        return markup;
                    },
                    templateSelection:function(post){
                        return post.text;
                    }
                });

            });

    });

    $('.wpbooking-metabox-template').on('wpbooking_change_service_type_metabox',function(){
        var me=$(this);
        me.find('.wpbooking-tabs').tabs();

        //Popover
        $('.wb-help-popover').popover({
            //container:'body',
            template:'<div class="popover wb-help-popover-el" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
        });

        load_gmap();

        $('.icp-auto').iconpicker();

        // Load Condition
        $('.wpbooking-form-group').on( 'change.conditionals', condition_object, function(e) {
            run_condition_engine();
        });


    });

    $('[name=service_type]').change(function(){
        var v=$(this).val();
        var metabox_template=$('.wpbooking-metabox-template');
        var t=$('#tmpl-wpbooking-metabox-'+v).html();
        metabox_template.html(t);
        if(t===undefined) $('.wpbooking-metabox-template').html('');

        metabox_template.trigger('wpbooking_change_service_type_metabox');


        var container = $(this).closest('.list-radio');
        container.find('[name=service_type]').closest('.wb-radio-button').removeClass('active');
        $(this).closest('.wb-radio-button').addClass('active');

    });
    $('[name=service_type][checked=checked]').trigger('change');


    $(document).on('click','.open_section_metabox',function(){
        $('.open_section_metabox').removeClass('active');
        $(this).addClass("active");
    });

    $(document).on('mouseenter', '.phone_country_number .input-group-addon', function() {
        $(this).find('.list_phone_country_number').show();
    });
    $(document).on('mouseleave', '.phone_country_number .input-group-addon', function() {
        var $this = $(this);
        $this.find('.list_phone_country_number').hide();
    });
    $(document).on('click','.phone_country_number .list_phone_country_number li',function(){
        var $value = $(this).data('code');
        var $country = $(this).data('country');
        var $container = $(this).closest('.phone_country_number');
        $container.find('.phone_code').val($value);
        $container.find('.demo-flag').attr("class","demo-flag flag-icon flag-icon-"+$country);
        $container.find('.list_phone_country_number').hide();

    });
    $(document).on('click','.radio_pro input',function(){
        var $container = $(this).closest('.st-metabox-content-wrapper');
        $container.find('.radio_pro').removeClass('checked');
        $(this).closest('.radio_pro').addClass('checked');
    // Report dropdown
    });
    $(document).on('click','.wb-repeat-dropdown-add',function(){
        var parent=$(this).closest('.form-group');
        var item=parent.find('.default-item').html();
        parent.find('.add-more-box').append('<div class="more-item">'+item+'<span class="wb-repeat-dropdown-remove"><i class="fa fa-trash"></i> </span></div>');

    });
    $(document).on('click','.wb-repeat-dropdown-remove',function(){
        $(this).closest('.more-item').remove();

    });
    $(document).on('change','.taxonomy_room_select .item_all',function(){
        var container = $(this).closest('.wpbooking-row');
        if ($(this).is(":checked"))
        {
            container.find('.item_base').prop('checked', true);
            container.find('.item_custom').prop('checked', false);
        }else{
            container.find('.item_base').prop('checked', false);
            container.find('.item_custom').prop('checked', false);
        }
        container.find('.item_post').prop('checked', false);
        container.find('.list_post').hide();
    });
    $(document).on('change','.taxonomy_room_select .item_custom',function(){
        var container = $(this).closest('.wpbooking-row');
        if ($(this).is(":checked"))
        {
            container.find('.list_post').show();
            container.find('.item_all').prop('checked', false);
        }else{
            container.find('.item_post').prop('checked', false);
            container.find('.list_post').hide();
        }
        container.find('.item_base').prop('checked', false);
    });
    $(document).on('change','.taxonomy_room_select .item_post',function(){
        var container = $(this).closest('.wpbooking-row');
        var container2 = $(this).closest('.list_post');
        var check = false;
        container2.find('.item_post').each(function(){
            if ($(this).is(":checked"))
            {
                check = true;
            }
        });
        if (check == true)
        {
            container.find('.item_base').prop('checked', true);
        }else{
            container.find('.item_base').prop('checked', false);
        }
    });

    $(document).on('change','.taxonomy_fee_select .term-checkbox',function(){
        if($(this).attr('checked')){
            $(this).closest('.term-item').addClass('active');
        }else{
            $(this).closest('.term-item').removeClass('active');
        }
    });
    $(document).on('click','.st-metabox-wrapper .content-accodition',function(){
        var container = $(this).closest('.open_section_metabox');
        if($(this).hasClass('active')){
            $(this).removeClass('active');
            $(this).find('.fa ').attr('class','fa fa-chevron-down');
            container.find('.content-metabox').addClass('no-active');
        }else{
            $(this).addClass('active');
            $(this).find('.fa ').attr('class','fa fa-chevron-up');
            container.find('.content-metabox').removeClass('no-active');
        }
    });

    // Create Room
    $(document).on('click','.hotel_room_list .create-room',function(){
        var parent=$(this).closest('.st-metabox-tab-content-wrap');
        showRoomForm(parent,0,$(this).data('hotel-id'));
        return false;
    });

    // Room Edit
    $(document).on('click','.hotel_room_list .room-edit',function(){
        var parent=$(this).closest('.st-metabox-tab-content-wrap');
        var room_id=$(this).data('room_id');
        showRoomForm(parent,room_id);
        return false;
    });

    // Delete Room
    $(document).on('click','.hotel_room_list .room-delete',function(){
        var t = $(this);
        var parent=$(this).closest('.st-metabox-tab-content-wrap');
        var gallery_container = parent.closest('.wpbooking-tabs').find('.featuredgallerydiv');
        var room_id=$(this).data('room_id');
        var del_security = $(this).data('del-security');
        var confirm_text = $(this).data('confirm');
        var wn = confirm(confirm_text);
        if(wn == true){
            parent.addClass('on-loading');
            $.ajax({
                type: 'post',
                dataType: 'json',
                data: {
                    action: 'wpbooking_del_room_item',
                    wb_room_id: room_id,
                    wb_del_security : del_security
                },
                url:wpbooking_params.ajax_url,
                success:function(res){
                    parent.removeClass('on-loading');
                    var count_old = parent.find('.room-count .n').text();
                    if(res.status == 1){
                        gallery_container.attr('data-room',res.data.list_room)
                        var count_new = parseInt(count_old) - 1;
                        parent.find('.room-count .n').text(count_new);
                        if(count_new <= 1){
                            parent.find('.room-count b').text(wpbooking_params.room);
                        }
                        t.closest('.room-item').remove();
                    }else{
                        alert(res.message);
                    }
                    if(typeof  res.updated_content!='undefined'){

                        for (var k in res.updated_content){
                            var element=$(k);
                            element.replaceWith(res.updated_content[k]);
                            $(window).trigger('wpbooking_event_hotel_room_update_content',[k,res.updated_content[k]]);
                        }
                    }
                    if(parent.find('.wb-room-list .room-item').length == 0 || parent.find('.wb-room-list').html() == undefined){
                        parent.find('.hotel_room_list').addClass('wpbooking-no-room');
                    }
                },
                error:function(e){
                    parent.removeClass('on-loading');
                    console.log(e.responseText);
                }
            });
        }
        return false;
    });

    // To All Rooms
    $(document).on('click','.wb-room-form .wb-all-rooms',function(){
        var parent=$(this).closest('.st-metabox-tab-content-wrap');
        var room_form=parent.find('.wpbooking-hotel-room-form');
        room_form.html('');
        parent.removeClass('on-create');
        parent.removeClass('wb-edit-room');

        var h=$('#st_post_metabox').offset().top;
        $('html,body').animate({'scrollTop':parseInt(h)-200});

        return false;
    });
    $(document).on('click','.wb-back-all-rooms',function(){
        $('.wb-back-all-rooms-wrap').unstick();
        var parent=$(this).closest('.st-metabox-tab-content-wrap');
        var room_form=parent.find('.wpbooking-hotel-room-form');
        room_form.html('');
        parent.removeClass('on-create');
        parent.removeClass('wb-edit-room');

        var h=$('#st_post_metabox').offset().top;
        $('html,body').animate({'scrollTop':parseInt(h)-200});

        return false;
    });



    // Save Room Data
    $(document).on('click','.wb-room-form .wb-save-room',function(){
        var parent=$(this).closest('.st-metabox-tab-content-wrap');
        var gallery_container = parent.closest('.wpbooking-tabs').find('.featuredgallerydiv');
        var room_form=$(this).closest('.wpbooking-hotel-room-form');
        var data=room_form.find('input,select,textarea').serialize();
        data+='&action=wpbooking_save_hotel_room';
        parent.addClass('on-loading');

        $.ajax({
            type:'post',
            data:data,
            dataType:'json',
            url:wpbooking_params.ajax_url,
            success:function(res){
                parent.removeClass('on-loading');
                var count_old = parent.find('.room-count .n').text();
                if(res.status){
                    // Go to All Rooms
                    //room_form.html('');
                    if(!parent.hasClass('wb-edit-room')){
                        var count_new = parseInt(count_old) + 1;
                        parent.find('.room-count .n').text(count_new);
                        if(count_new > 1){
                            parent.find('.room-count b').text(wpbooking_params.rooms);
                        }
                        var html = parent.find('.room-item-default .room-item').clone();
                        html.find('.room-remain-left').html(res.data.number+' room(s)');
                        html.find('.room-image').html(res.data.thumbnail);
                        html.find('.room-type').html(res.data.title);
                        html.find('.room-edit').attr('data-room_id',res.data.room_id);
                        html.find('.room-delete').attr('data-room_id',res.data.room_id);
                        html.find('.room-delete').attr('data-del-security',res.data.security);
                        gallery_container.attr('data-room',res.data.list_room);
                        parent.find('.hotel_room_list .wb-room-list').append(html);
                    }else{
                        var item_html = parent.find('.item-hotel-room-'+res.data.room_id);
                        item_html.find('.room-remain-left').html(res.data.number+' room(s)');
                        item_html.find('.room-type').html(res.data.title);
                    }
                    //parent.removeClass('on-create');
                    //parent.removeClass('wb-edit-room');

                    if(typeof  res.updated_content!='undefined'){

                        for (var k in res.updated_content){
                            var element=$(k);
                            element.replaceWith(res.updated_content[k]);
                            $(window).trigger('wpbooking_event_hotel_room_update_content',[k,res.updated_content[k]]);
                        }

                    }

                    $(window).trigger('wpbooking_event_hotel_room_saved');
                    $('input[name=room_measunit]').trigger('change');
                    parent.find('.hotel_room_list').removeClass('wpbooking-no-room');
                }else{
                    if(typeof res.message!='undefined'){
                        alert(res.message);
                    }
                }

                if(typeof res.error_fields!=='undefined')
                {
                    for(var k in res.error_fields){

                        var field=parent.find("[name='"+k+"']");
                        if(!field.length){
                            field=parent.find('.field-'+k+' .st-metabox-content-wrapper .form-group');
                        }
                        field.addClass('is_error');
                        $('<span class="is_error_message">'+res.error_fields[k]+'</span>').insertAfter(field);
                    }

                    var first_error=parent.find('.is_error:first-child');
                    if(first_error.length){
                        var h = parent.find(first_error).offset().top;
                        $('html,body').animate({'scrollTop': parseInt(h) - 200});
                    }

                }

            },
            error:function(e){
                parent.removeClass('on-loading');
                console.log(e.responseText);
            }
        });
        return false;
    });


    function showRoomForm(parent,room_id,hotel_id) {
        var room_form=parent.find('.wpbooking-hotel-room-form');
        parent.addClass('on-loading');

        var edit_room_class = '';
        if(room_id===undefined) {
            room_id = 0;
        }
        if(hotel_id===undefined){
            edit_room_class = 'wb-edit-room';
            hotel_id=0;
        }

        $.ajax({
            url:wpbooking_params.ajax_url,
            dataType:'json',
            type:'post',
            data:{
                action:'wpbooking_show_room_form',
                room_id:room_id,
                hotel_id:hotel_id
            },
            success:function(res){
                parent.removeClass('on-loading');
                if(res.status){
                    room_form.html(res.html);
                    run_condition_engine();
                    filterRoomName();
                    parent.addClass('on-create');
                    parent.addClass(edit_room_class);
                    $(window).trigger('wpbooking_show_room_form',room_form);
                }
                $('#room_type').trigger("change");
                $('#bed_rooms').trigger("change");
                $('#living_rooms').trigger("change");
                $('#room_name').trigger("keypress");
                $('input[type=number]').trigger("change");
                $('.wb-back-all-rooms-wrap').sticky({topSpacing:30});
                if(res.message){ alert(res.message);}
            },
            error:function(e){
                console.log(e.responseText);
                parent.removeClass('on-loading');
            }

        })
    }
    $(document).on('change','#room_type',function(){
        var val=$(this).val();
        filterRoomName(val);
        changeBedRoomOption(val,$(this));

    });
    $(document).on('keypress','#room_name',function(){
        var $this = $(this);
        var parent = $this.closest('.content-metabox');
        setTimeout(function(){
            var title = $this.val();
            parent.find('.field-title').html(wpbooking_params.room_name+' '+title);
        },100);
    });


    function filterRoomName(parent){
        $('#room_name option[parent='+parent+']').show();
        $('#room_name option[parent!='+parent+']').hide();
    }

    function changeBedRoomOption(val,el) {
        if(el.find('option[value='+val+']').attr('muilti_bedroom')){
            $('.wpbooking-settings.bed_options').addClass('is-multi-room');
        }else{
            $('.wpbooking-settings.bed_options').removeClass('is-multi-room');
        }
    }


    /*$(document).on('change','#bed_rooms',function(){
        var parent=$('.bed_options');
        var number = parseInt($(this).val());

        var item=parent.find('.multi-item-default').html();
        var number_check = 1;
        parent.find('.multi-item-row').each(function(){
            if(number < number_check){
                $(this).remove();
            }
            number_check++;
        });
        if(number > (number_check - 1)){
            var n_item = number - (number_check - 1);
            for ( var i=0 ; i < n_item ; i++){
                console.log(i);
                var n_item_next = number_check +i;
                console.log(item);
                var html = item.split('__number_room__').join(n_item_next);
                parent.find('.multi-bed-option').append('<div class="multi-item-row number_'+n_item_next+'" data-number="'+n_item_next+'">'+html+'</div>');
            }
        }
    });

    $(document).on('change','#living_rooms',function(){
        var parent=$('.living_options');
        var number = parseInt($(this).val());

        var item=parent.find('.multi-item-default').html();
        var number_check = 1;
        parent.find('.multi-item-row').each(function(){
            if(number < number_check){
                $(this).remove();
            }
            number_check++;
        });
        if(number > (number_check - 1)){
            var n_item = number - (number_check - 1);
            for ( var i=0 ; i < n_item ; i++){
                console.log(i);
                var n_item_next = number_check +i;
                console.log(item);
                var html = item.split('__number_living__').join(n_item_next);
                parent.find('.multi-living-options').append('<div class="multi-item-row number_'+n_item_next+'" data-number="'+n_item_next+'">'+html+'</div>');
            }
        }
    });*/

    $('.check_all_service_type').change(function(){
       if($(this).attr('checked')=='checked'){
           $(this).closest('li').siblings().find('input:checkbox').attr('checked','checked')
           $(this).closest('form').submit();
       }else{
           $(this).closest('li').siblings().find('input:checkbox').removeAttr('checked');
       }
    });

    $('.service_types input[type=checkbox]:not(.check_all_service_type)').change(function(){
        $(this).closest('form').submit();
    });

    $('.datepicker_start').datepicker();
    $('.datepicker_end').datepicker();

    $('.do-search').click(function () {
        var form=$(this).closest('.report-form');
        form.find('[name=report_type]').val('date_range');
        var is_validated=true,datepicker_start,datepicker_end;

        datepicker_start=form.find('.datepicker_start');
        datepicker_end=form.find('.datepicker_end');


        if(datepicker_start.val()==''){
            is_validated=false;
            datepicker_start.addClass('error');
        }

        if(datepicker_end.val()==''){
            is_validated=false;
            datepicker_end.addClass('error');
        }

        if(is_validated){
            form.submit();
        }

    });
    $('.change-report').click(function () {
        var form=$(this).closest('.report-form');
        form.find('.filter-date input').val('');
        form.find('[name=report_type]').val($(this).data('range'));
        form.submit();
    });

    $('.select-report-type').change(function () {
        var form=$(this).closest('.report-form');
        form.find('.filter-date input').val('');
        form.find('[name=report_type]').val($(this).val());
        form.submit();
    });

    $(document).on('change','.age_adult_max',function(){
        if(parseInt($(this).val()) < parseInt($(this).closest('.wb-age-options-table').find('.age_adult_min').val())){
            $('.adult_notice').show();
        }else{
            $('.adult_notice').hide();
        }
    });

    $(document).on('change','.age_child_max',function(){
        if(parseInt($(this).val()) < parseInt($(this).closest('.wb-age-options-table').find('.age_child_min').val())){
            $('.adult_notice').show();
        }else{
            $('.adult_notice').hide();
        }
    });

    $(document).on('change','.age_infant_max',function(){
        if(parseInt($(this).val()) < parseInt($(this).closest('.wb-age-options-table').find('.age_infant_min').val())){
            $('.adult_notice').show();
        }else{
            $('.adult_notice').hide();
        }
    });

    //$(document).on('change','input[type=number]',function(){
    //    $(this).each(function(){
    //        var number = $(this).val();
    //        number = parseFloat(number);
    //        //console.log(number);
    //        if (isNaN(number)) {
    //            var min = $(this).attr('min');
    //            if(min != undefined && min != ''){
    //                number = min;
    //            }
    //
    //        }
    //        $(this).val(number);
    //    });
    //});

    $('.wb-button-customer').each(function(){
        $(this).click(function(){
            if($(this).parent().find('.wb-customer-detail').hasClass('none')){
                $(this).parent().find('.wb-customer-detail').removeClass('none');
            }else{
                $(this).parent().find('.wb-customer-detail').addClass('none');
            }
        });
    });

    $('.wp-button-booking').each(function(){
        $(this).click(function(){
            var detail = $(this).closest('.wb-booking-information').find('.wb-booking-detail');
            if(detail.hasClass('none')){
                detail.removeClass('none');
            }else{
                detail.addClass('none');
            }
        });
    });

    $(window).load(function(){
        $('.wb-column-action').parent().each(function(){
            var elm = $(this).find('.wb-row-actions');
            elm.addClass('none');
            $(this).hover(function () {
                elm.removeClass('none');
            },function(){
                elm.addClass('none');
            });
        });
    });


    $('#pricing_type').change(function () {
        if($('.calendar-room2.tour').hasClass('per_person')){
            $('.calendar-room2.tour').removeClass('per_person');
            $('.calendar-room2.tour').addClass('per_unit');
        }else{
            $('.calendar-room2.tour').removeClass('per_unit');
            $('.calendar-room2.tour').addClass('per_person');
        }
    });

    $(document).on('click','.btn_list_item_edit', function (e) {
        e.preventDefault();
        var parent = $(this).closest('.wpbooking-list-item');
        if(parent.find('table').hasClass('hidden')){
            parent.find('table').removeClass('hidden');
            parent.addClass('active');
        }else{
            parent.find('table').addClass('hidden');
            parent.removeClass('active');
        }
    });

    $('.wb-itinerary-add-new').click(function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var item = $('.item-itinerary-draft').html();

        $(this).closest('.wb-itinerary-wrap').find('.itinerary-content').append(item);
        $(this).closest('.wb-itinerary-wrap').find('.itinerary-content').find('.input-title input').attr('name',id+'[title][]');
        $(this).closest('.wb-itinerary-wrap').find('.itinerary-content').find('.input-desc textarea').attr('name',id+'[desc][]');
    });

    $(document).on('click','.item-itinerary-del', function(e){
        e.preventDefault();
        $(this).closest('.item-itinerary').remove();
    });

    //extensionh page ajax

    $('.wb-search-extension').click(function () {
        $.ajax({
            url: wpbooking_params.api_url,
            data: {
                action: 'st_get_extension',
                s: $(this).closest('.search-extensions').find('.search-field').val()
            },
            type: 'post',
            dataType: 'jsonp',
            jsonp: 'callback',
            jsonpCallback: 'callback',
            beforeSend:function(){
                var loading = '<div class="ex-loading"></div>';
                $('.extension-list').append(loading);
            },
            success:function(res){
                //console.log(res.responseText);
                $('.ex-loading').remove();
                $('.extension-list ').html('');
                $('.extension-list ').append('<div class="list"></div>');
                if(res.status == 1){
                    res.data.posts.forEach(function(item,index){
                        var item_html = '<div class="item">' +
                            '<div class="extension">' +
                            '<div class="thumnail">' +
                            '<img src="'+item.thumb_url+'"/>' +
                            '</div>' +
                            '<div class="info">' +
                            '<h3 class="title">'+item.title+'</h3>' +
                            '<p class="desc">'+item.short_ex+'</p>' +
                            '<a class="read-more" target="_blank" href="'+item.url+'">'+wpbooking_params.read_more+'</a>' +
                            '</div>' +
                            '</div>' +
                            '</div>' +
                            '</div>';
                        $('.extension-list .list').append(item_html);
                    });
                    var s = '';
                    if(res.s){
                        s = res.s;
                    }

                    var page_html = '<div class="pagination"><ul class="ex-pagination">';

                    page_html += '<li class="hidden"><a href="#" data-paged="0" class="prev">'+wpbooking_params.prev+'</a></li>';

                    for(var i = 1; i<= res.data.max_pages; i++){
                        if(i==1){
                            page_html += '<li class="active"><span>'+i+'</span></li>';
                        }else{
                            page_html += '<li><a href="#" data-paged="'+i+'" data-s="'+s+'">'+i+'</a></li>';
                        }

                    }
                    page_html += '<li><a href="#" data-paged="2" class="next" data-s="'+s+'" >'+wpbooking_params.next+'</a></li>';
                    page_html += '</ul></div>';

                    if(res.data.max_pages > 1) {
                        $('.extension-list').append(page_html);
                    }

                    $('.result-text .ex-total').text(res.data.post_count);
                    $('.result-text .ex-from').text(1);
                    if( res.data.post_count >= res.posts_per_page)
                        $('.result-text .ex-to').text(res.posts_per_page);
                    else
                        $('.result-text .ex-to').text(res.data.post_count);

                }else{
                    $('.extension-list .list').append('<h3>No Result</h3>');
                    $('.result-text').hide();
                }

            },
            error:function(e){
                console.log(e);
            }
        });
        return false;
    });
    $(document).on('click', '.ex-pagination a', function(){
        $.ajax({
            url: wpbooking_params.api_url,
            data: {
                action: 'st_get_extension',
                paged: $(this).attr('data-paged'),
                cat_id: $(this).attr('data-cat'),
                s: $(this).attr('data-s'),

            },
            type: 'post',
            dataType: 'jsonp',
            jsonp: 'callback',
            jsonpCallback: 'callback',
            beforeSend:function(){
                var loading = '<div class="ex-loading"></div>';
                $('.extension-list').append(loading);
            },
            success:function(res){
                $('.ex-loading').remove();
                $('.extension-list').html('');
                $('.extension-list').append('<div class="list"></div>');
                if(res.status == 1){
                    res.data.posts.forEach(function(item,index){
                        var item_html = '<div class="item">' +
                            '<div class="extension">' +
                            '<div class="thumnail">' +
                            '<img src="'+item.thumb_url+'"/>' +
                            '</div>' +
                            '<div class="info">' +
                            '<h3 class="title">'+item.title+'</h3>' +
                            '<p class="desc">'+item.short_ex+'</p>' +
                            '<a class="read-more" target="_blank" href="'+item.url+'">'+wpbooking_params.read_more+'</a>' +
                            '</div>' +
                            '</div>' +
                            '</div>' +
                            '</div>';
                        $('.extension-list .list').append(item_html);
                    });
                    var s = '';
                    if(res.s && res.s != undefined){
                        s = res.s;
                    }

                    var cat = '';
                    if(res.cat_id && res.cat_id != undefined){
                        cat = res.cat_id;
                    }

                    var prev = '';
                    var next = '';
                    if(res.paged == 1){
                        prev = 'hidden';
                    }
                    if(res.paged == res.data.max_pages){
                        next = 'hidden';
                    }

                    var page_html = '<div class="pagination"><ul class="ex-pagination">';
                    page_html += '<li class="'+prev+'"><a href="#" data-s="'+s+'" data-cat="'+cat+'" data-paged="'+(parseInt(res.paged) - 1)+'" class="prev">'+wpbooking_params.prev+'</a></li>';

                    for(var i = 1; i<= res.data.max_pages; i++){
                        if(res.paged == i){
                            page_html += '<li class="active"><span>'+i+'</span></li>';
                        }else{
                            page_html += '<li><a href="#" data-s="'+s+'" data-cat="'+cat+'" data-paged="'+i+'">'+i+'</a></li>';
                        }

                    }

                    page_html += '<li class="'+next+'"><a href="#" data-paged="'+(parseInt(res.paged) + 1)+'" data-cat="'+cat+'" data-s="'+s+'" class="next">'+wpbooking_params.next+'</a></li>';
                    page_html += '</ul></div>';

                    $('.extension-list').append(page_html);

                    $('.result-text .ex-total').text(res.data.post_count);
                    $('.result-text .ex-from').text(parseInt(res.paged-1)*parseInt(res.posts_per_page));
                    if(res.paged == 1){
                        $('.result-text .ex-from').text(1);
                    }
                    console.log(res.data.post_count);
                    $('.result-text .ex-to').text(res.posts_per_page*res.paged);
                    if(res.paged == res.data.max_pages){
                        $('.result-text .ex-to').text(res.data.post_count);
                    }
                }
            },
            error:function(e){
                console.log(e);
            }
        });
        return false;
    });

    $('.box-categories .list-cat a').each(function(){
        $(this).click(function(){
            $('.box-categories .list-cat').find('li').removeClass('active');
            $(this).parent().addClass('active');
            $.ajax({
                url: wpbooking_params.api_url,
                data: {
                    action: 'st_get_extension',
                    cat_id: $(this).attr('data-id')
                },
                dataType: 'jsonp',
                jsonp: 'callback',
                jsonpCallback: 'callback',
                type: 'post',
                beforeSend:function(){
                    var loading = '<div class="ex-loading"></div>';
                    $('.extension-list').append(loading);
                },
                success:function(res){
                    $('.ex-loading').remove();
                    $('.extension-list').html('');
                    $('.extension-list').append('<div class="list"></div>');
                    if(res.status == 1){
                        res.data.posts.forEach(function(item,index){
                            var item_html = '<div class="item">' +
                                '<div class="extension">' +
                                '<div class="thumnail">' +
                                '<img src="'+item.thumb_url+'"/>' +
                                '</div>' +
                                '<div class="info">' +
                                '<h3 class="title">'+item.title+'</h3>' +
                                '<p class="desc">'+item.short_ex+'</p>' +
                                '<a class="read-more" target="_blank" href="'+item.url+'">'+wpbooking_params.read_more+'</a>' +
                                '</div>' +
                                '</div>' +
                                '</div>' +
                                '</div>';
                            $('.extension-list .list').append(item_html);
                        });

                        var cat = '';
                        if(res.cat_id && res.cat_id != undefined){
                            cat = res.cat_id;
                        }

                        var page_html = '<div class="pagination"><ul class="ex-pagination">';

                        page_html += '<li class="hidden"><a href="#" data-paged="0" class="prev">'+wpbooking_params.prev+'</a></li>';

                        for(var i = 1; i<= res.data.max_pages; i++){
                            if(i==1){
                                page_html += '<li class="active"><span>'+i+'</span></li>';
                            }else{
                                page_html += '<li><a href="#" data-paged="'+i+'" data-cat="'+cat+'">'+i+'</a></li>';
                            }

                        }
                        page_html += '<li><a href="#" data-paged="2" class="next" data-cat="'+cat+'" >'+wpbooking_params.next+'</a></li>';
                        page_html += '</ul></div>';

                        if(res.data.max_pages > 1) {
                            $('.extension-list').append(page_html);
                        }

                        $('.result-text .ex-total').text(res.data.post_count);
                        $('.result-text .ex-from').text(1);
                        if( res.data.post_count >= res.posts_per_page)
                            $('.result-text .ex-to').text(res.posts_per_page);
                        else
                            $('.result-text .ex-to').text(res.data.post_count);
                    }

                },
                error:function(e){
                    console.log(e);
                }
            });
            return false;
        });
    });

});