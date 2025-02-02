$(document).ready(function(){
    $('.slider__photos').slick({
        adaptiveHeight:true,
        slidesToShow:3,
        touchThreshold: 15,
        waitForAnimate: true,
        variableWidth: true,
    });
});


$(document).ready(function() {
    $('.header__burger').click(function(event){
        $('.header__burger, .header__links, .header__message').toggleClass('active');
       $('body').toggleClass('lock');
    });
});