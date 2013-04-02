
var announcementsUI = {};
announcementsUI.nFields = 0;

announcementsUI.prioritySlider = function() {

    var startValue = $( '#priorityValue' ).val();

    $( '#prioritySlider' ).slider( {
        min: 0,
        max: 100,
        value: startValue,
        step: 5,
        slide: function( event, ui ) {
            $( '#priorityValue' ).val( ui.value );
            $( '#priorityValueView' ).text( ui.value );
        }
    } );

};

announcementsUI.initFromEventDate = function() {
    $( "#fromEventDate" ).datepicker( {
        changeMonth:true,
        changeYear:true,
        minDate: 0,
        onClose: function( dateText ) {
            if( dateText!='' ) {
                $( '#eventDate' ).datepicker( 'option', 'minDate', dateText );
            }
        }
    } );
};

announcementsUI.initEventDate = function() {

    $( "#eventDate" ).datepicker( {
        changeMonth: true,
        changeYear: true,
        minDate: 0,
        onClose: function( dateText ) {
            if( dateText!='' ) {
                $( '#beginDate, #endDate' ).removeAttr( 'disabled' );
                $( '#beginDate, #endDate' ).datepicker( "option", 'maxDate', dateText );
                $( '#endDate' ).val( dateText );
            } else {
                $( '#beginDate, #endDate' ).attr( 'disabled', 'disabled' );
                $( '#beginDate, #endDate' ).val( '' );
            }
        }
    } );
};

announcementsUI.initBeginDate = function() {

    $( "#beginDate, #endDate, #fromEventDate" ).datepicker( {
        changeMonth: true,
        changeYear: true,
        minDate: 0
    } );
};

announcementsUI.getPrioritySlider = function( aId, value ) {

    $( '#prioritySlider-' + aId ).slider( {
        min:0,
        max:100,
        value:value,
        step:5,
        slide:function ( event, ui ) {
            $( '#priorityValue-' + aId ).val( ui.value );
            $( '#priorityValueView-' + aId ).text( ui.value );
        }
    } );
    $( '#savePriorityButton-' + aId ).show();
};

announcementsUI.savePriority = function( aId ) {

    value = $( '#priorityValue-' + aId ).val();
    ajaxer.query( '/adm/announcements/savepriority/' + aId + '/' + value + '/' );
};

announcementsUI.editCategory = function( cId ) {

    if( $( '#ann-category-' + cId + '-edit' ).css( 'display' ) == 'none' ) {
        $( '#ann-category-' + cId ).slideUp( 'fast' );
        $( '#ann-category-' + cId + '-edit' ).slideDown( 'fast' );
    } else {
        $( '#ann-category-' + cId + '-edit' ).slideUp( 'fast' );
        $( '#ann-category-' + cId ).slideDown( 'fast' );
    }
};

announcementsUI.editAdditionals = function( cId ) {

    if( $( '#addField-' + cId + '-edit' ).css( 'display' ) == 'none' ) {
        $( '#addField-' + cId ).slideUp( 'fast' );
        $( '#addField-' + cId + '-edit' ).slideDown( 'fast' );
    } else {
        $( '#addField-' + cId + '-edit' ).slideUp( 'fast' );
        $( '#addField-' + cId ).slideDown( 'fast' );
    }
};

announcementsUI.addSchedule = function( ) {

    var $cloned = $( '#schedulesFieldAdd' ).clone( ).removeClass( 'no-display' ).removeAttr( 'id' );

    $cloned.find( 'input.sn' ).attr( 'name', 'scheduleField[' + announcementsUI.nFields + ']' );
    $cloned.find( 'input.sv' ).attr( 'name', 'scheduleValue[' + announcementsUI.nFields + ']' );
    $cloned.appendTo( '#schedulesFields' );
    announcementsUI.nFields++;
};
announcementsUI.deleteSchedule = function( elem ) {
    $( elem ).parent( 'div' ).remove();
};
announcementsUI.setScheduleCount = function( count ) {
    announcementsUI.nFields = count;
};

// стартуем
$( document ).ready( function () {
    announcementsUI.initFromEventDate();
    announcementsUI.initEventDate();
    announcementsUI.initBeginDate();
    announcementsUI.prioritySlider();
} );

$( document ).bind( 'construct', announcementsUI.initFromEventDate );
$( document ).bind( 'construct', announcementsUI.initEventDate );
$( document ).bind( 'construct', announcementsUI.initBeginDate );
$( document ).bind( 'construct', announcementsUI.prioritySlider );

