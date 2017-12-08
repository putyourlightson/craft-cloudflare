$(".purge-option.purge-individual-urls .heading").click(function(){
    var $heading = $(this);
    $(".purge-urls-form").slideToggle(100, function(e){
        $heading.hasClass('active') ? $heading.removeClass('active') : $heading.addClass('active');
    });
});

$(".purge-option.purge-all .heading").click(function(){
    if (confirm("You definitely want to purge the entire cache, right?")) {
        window.location.href = window.PURGE_ALL_ACTION;
    }
});
