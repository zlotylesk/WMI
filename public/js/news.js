$(function() {
    //$('body').nivoSlider(); 
    setInterval(function(){
        var s = $('.slide').last();
        s.animate({'left':'100%'},1000,function(){
            $('body').prepend($('.slide').last().clone().css({'left':'0'}));
            s.remove();
        });
    },10000);
});