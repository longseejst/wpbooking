/**
 * Created by Dungdt on 3/30/2016.
 */
jQuery(document).ready(function($){
    $('.wpbooking-rating-review a').hover(function(){
        var index=$(this).index();
        index=parseInt(index);

        $(this).addClass('active');
        $(this).prevAll().addClass('active');
        $(this).nextAll().removeClass('active');

        $(this).closest('.wpbooking-rating-review').find('.wpbooking_review_detail_rate').val(index+1);

        var totalRate=0;
        var rateStats=$('.wpbooking_review_detail_rate');
        if(rateStats.length){
            rateStats.each(function(){
                totalRate+=parseInt($(this).val());
            });
            $('[name=wpbooking_review]').val(parseFloat(totalRate/rateStats.length));
        }else{
            $('[name=wpbooking_review]').val(index+1);
        }


    });

    // Single Services
    // Helper functions
    function getFormData(form){
        var data=[];
        var data1 = form.serializeArray();
        for(var i = 0; i < data1.length; i++){
            data.push({
                name : data1[i].name,
                value : data1[i].value
            });
        }
        var dataobj = {};
        for (var i = 0; i < data.length; ++i){
            dataobj[data[i].name] = data[i].value;
        }

        return dataobj;
    };


    // Order Form
    $('.wpbooking_order_form .submit-button').click(function(){
        var form=$(this).closest('.wpbooking_order_form');
        form.find('[name]').removeClass('input-error');
        var me=$(this);
        me.addClass('loading').removeClass('error');
        form.find('.wpbooking-message').remove();

        data=form.serialize();

        $.ajax({
            url:wpbooking_params.ajax_url,
            data:data,
            dataType:'json',
            type:'post',
            success:function(res){
                if(res.status){
                    me.addClass('success');
                }else{
                    me.addClass('error');
                }
                if(res.message){
                    var message=$('<div/>');
                    message.addClass('wpbooking-message');
                    message.html(res.message);
                    me.after(message);
                }
                if(typeof  res.data!='undefined' && res.data.redirect){
                    window.location=res.data.redirect;
                }

                if(typeof res.error_fields!='undefined')
                {
                    for(var k in res.error_fields){

                        form.find("[name='"+k+"']").addClass('input-error');
                    }
                }
                if(typeof  res.updated_content!='undefined'){

                    for (var k in res.updated_content){
                        var element=$(k);
                        element.replaceWith(res.updated_content[k]);
                        $(window).trigger('wpbooking_event_cart_update_content',[k,res.updated_content[k]]);
                    }
                }

                me.removeClass('loading');
            },
            error:function(e){
                var message=$('<div/>');
                message.addClass('wpbooking-message');
                message.html(e.responseText);
                me.after(message);
                me.removeClass('loading').addClass('error');
            }
        })
    });

    // Checkout Form
    $('.wpbooking_checkout_form .submit-button').click(function(){
        var form=$(this).closest('.wpbooking_checkout_form');
        form.find('[name]').removeClass('input-error');
        var me=$(this);
        me.addClass('loading').removeClass('error');
        form.find('.wpbooking-message').remove();

        data=form.serialize();

        $.ajax({
            url:wpbooking_params.ajax_url,
            data:data,
            dataType:'json',
            type:'post',
            success:function(res){
                if(res.status){
                    me.addClass('success');
                }else{
                    me.addClass('error');
                }

                if(res.message){
                    var message=$('<div/>');
                    message.addClass('wpbooking-message');
                    message.html(res.message);
                    me.after(message);
                }
                if(typeof res.data !='undefined'&& typeof res.data.redirect !='undefined' && res.data.redirect){
                    window.location.href=res.data.redirect;
                }
                if(res.redirect){
                    window.location.href=res.redirect;
                }

                if(typeof res.error_fields!='undefined')
                {
                    console.log(res.error_fields);
                    for(var k in res.error_fields){
                        form.find("[name='"+k+"']").addClass('input-error');
                    }
                }
                me.removeClass('loading');
            },
            error:function(e){
                console.log(e);
                me.removeClass('loading').addClass('error');
                var message=$('<div/>');
                message.addClass('wpbooking-message');
                message.html(e.responseText);
                me.after(message);
            }
        })
    });



    //////////////////////////////////
    /////////// Google Gmap //////////
    //////////////////////////////////

    $('.wpbooking_google_map').each(function(){
        var map_lat = $(this).data('lat');
        var map_lng = $(this).data('lng');
        var map_zoom = $(this).data('zoom');
        console.log(map_zoom);
        $(this).gmap3({
            map:{
                options:{
                    center:[map_lat,map_lng],
                    zoom: map_zoom
                }
            },
            marker:{
                values:[
                    {latLng:[map_lat, map_lng]},
                ],
                options:{
                    draggable: false
                }
            }
        });
    });

    // Gateway Items
    $('.wpbooking-gateway-item [name=payment_gateway]').change(function(){
       var parent=$(this).closest('.wpbooking-gateway-item');
        if(!parent.hasClass('active'))
        {
            parent.siblings().removeClass('active');
            parent.addClass('active');
        }
    });


    $(document).on('click','.item-search .item_taxonomy',function(){
        var container  = $(this).parent().parent();
        var list = "";
        container.find(".item_taxonomy").each(function(){
            if($(this).attr('checked')) {
                list +=  $(this).val()+',';
            }
        });
        container.find('.data_taxonomy').val(list.substring(0,list.length - 1));
    });

    var has_date_picker=$('.has-date-picker');
    has_date_picker.datepicker();
    var datepicker=has_date_picker.datepicker('widget');
    datepicker.wrap('<div class="ll-skin-melon"/>');

    $('.wpbooking-date-start').datepicker(
        {
            minDate:0,
            onSelect:function(selected) {
                var form=$(this).closest('form');
                var date_end=$('.wpbooking-date-end',form);
                date_end.datepicker("option","minDate", selected)

            }
        });
    datepicker=$('.wpbooking-date-start').datepicker('widget');
    datepicker.wrap('<div class="ll-skin-melon"/>');

    $('.wpbooking-date-end').datepicker( {
        minDate:0,
        onSelect:function(selected) {
            var form=$(this).closest('form');
            var date_end=$('.wpbooking-date-start',form);
            date_end.datepicker("option","maxDate", selected)

        }
    });
    datepicker=$('.wpbooking-date-end').datepicker('widget');
    datepicker.wrap('<div class="ll-skin-melon"/>');

    $('.bravo-select2').select2();

    /**
     * Show More Search Fields
     * @author dungdt
     * @since 1.0
     */
    $('.wpbooking-show-more-fields').click(function(){
        var parent=$(this).parent();

        parent.find('.wpbooking-search-form-more').slideDown('fast');
        $(this).hide();
    });

    /**
     * Hide More Search Fields
     * @author dungdt
     * @since 1.0
     */
    $('.wpbooking-hide-more-fields').click(function(){
        var parent=$(this).closest('.wpbooking-search-form-more-wrap');

        parent.find('.wpbooking-search-form-more').slideUp('fast',function(){

            parent.find('.wpbooking-show-more-fields').show();
        });
    });

    /**
     * Ion-RangeSlider for Price Search Field
     * @author dungdt
     * @since 1.0
     */
    $('.wpbooking-ionrangeslider').each(function(){
        if(typeof $.fn.ionRangeSlider=='undefined') return false;
        var min=$(this).data('min');
        var max=$(this).data('max');
        var type=$(this).data('type');
        $(this).ionRangeSlider({
            min: min,
            max: max,
            type:type
        });
    });
});

