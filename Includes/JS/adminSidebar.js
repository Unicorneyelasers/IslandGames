$(document).ready( function() {
    collapseSidebar();

    $(".collapsible").click(function(evt) {
        $(evt.target).toggleClass("sidebarActive");

        let content = $(evt.target).children(".dropdownData");
        let icon = $(evt.target).children("a").children("span");

        $(content).slideToggle(300);
        if ($(icon).html() == '<i class="fas fa-arrow-down"></i>') {
            $(icon).html('<i class="fas fa-arrow-up"></i>');
        } else {
            $(icon).html('<i class="fas fa-arrow-down"></i>');
        }
    });
})

function collapseSidebar() {
    $( ".collapsible" ).each(function() {
        let div = $(this).children("div");
        $(div).toggle();
    });
}