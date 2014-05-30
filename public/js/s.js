


$( document ).ready(function() {
    $('.szukaj').click(function (e){
        //$('body').css({'background':'black'});
        e.preventDefault();
        $(' + form',this).toggle();
    });
    $('.ogloszenie img').removeAttr('width').removeAttr('height');
    $('.ogloszenie iframe').attr('width','100%').attr('height','80%');
    if($('#exp').val()){$('#exp').val($('#exp').val().substring(0, 10));}
    $('img').each(function(){
        if(
            $(this).parent().width() < $(this).width()
        ){
                $(this).width('100%').removeAttr('width').removeAttr('height');
        }
    });
    
});