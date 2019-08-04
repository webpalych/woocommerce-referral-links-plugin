jQuery(document).ready(function ($) {

    var refLinks = $('.ref_link_admin>a');
    refLinks.on('click', function (e) {
        e.preventDefault();
        var link = $(this).attr('href');
        copyToClipboard(link);
        alert(wcRefLocalize.alertMessage +": " + link);
    });

    function copyToClipboard(text) {
        var temp = $("<input>");
        $("body").append(temp);
        temp.val(text).select();
        document.execCommand("copy");
        temp.remove();
    }

});


