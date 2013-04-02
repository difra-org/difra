var newsUI = {};

newsUI.initDatepicker = function() {
    $( "#pubDate, #viewDate, #stopDate" ).datepicker( {
        changeMonth:true,
        changeYear:true
    } );
};
$( document ).ready( function() { newsUI.initDatepicker(); } );
$( document ).bind( 'construct', newsUI.initDatepicker );