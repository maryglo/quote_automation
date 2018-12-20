jQuery(document).ready(function($){
    $('#tvb_submit').click(function(e){
        var form = $('#tvb_request_quote_form');

        if(form.valid()){
            $.ajax({
                url: tvb_quote_automation_object.ajaxurl,
                method:'post',
                beforeSend: function(){
                    $('.tvb_loader').show();
                    $('#tvb_submit').attr('disabled', true);
                },
                data:{'action':'tvb_post_dealers_data','post_data': form.serialize()},
                success:function(response){
                    if(response.success == false){
                        $('#tvb_submit').attr('disabled', false);
                        $('.tvb_loader').hide();
                        $('.tvb_wrapper').find('.alert-danger').html("<strong>Error!</strong> "+response.message).show();
                    } else {
                        $('#tvb_request_quote_form').remove();
                        $('.tvb_wrapper').find('.alert-success').html("<strong>Success!</strong> "+response.message).show();
                    }
                }
            });
            e.preventDefault();
        }
    });

    $('#tvb_dealer_submit').click(function(e){
        var form = $('#tvb_dealer_quote_form');

        if(form.valid()){
            $(this).attr('disabled', true);
            $(this).next('.tvb_loader').show();
            form.submit();
        }
    });

    $('.dealers_opts').on('change',function(){
       var elem = $(this);
       var position = $(this).data('position');
       var dealer_id = $('option:selected', this).data('id');
       var dealer_name = $('option:selected', this).data('name');

       $('input#tvb_DealerId_'+position).val(dealer_id);
       $('input#tvb_DealerName_'+position).val(dealer_name);
    });
});