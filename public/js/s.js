


$( document ).ready(function() {
    $('.szukaj').click(function (e){
        //$('body').css({'background':'black'});
        e.preventDefault();
        $(' + form',this).toggle();
    });
    $('.ogloszenie img').removeAttr('width').removeAttr('height');
    $('#exp').val($('#exp').val().substring(0, 10));
    
});