


$( document ).ready(function() {
    $('.szukaj').click(function (e){
        //$('body').css({'background':'black'});
        e.preventDefault();
        $(' + form',this).toggle();
    });
});