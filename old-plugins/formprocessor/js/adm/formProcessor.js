
var formProcessor = {};
formProcessor.nFields = 0;

formProcessor.createField = function() {

    $( '#fieldType' ).removeClass( 'invalid' );

    selectedValue = $( '#fieldType :selected' ).val();
    if( typeof selectedValue == "undefined" || selectedValue == 0 || !$( '#formTypes > .formField' ).is( '.type-' + selectedValue ) ) {
        $( '#fieldType' ).addClass( 'invalid' );
        return;
    }
    // клонируем элемент
    formProcessor.nFields += 1;
    $( '#formTypes > .type-' + selectedValue ).clone().attr( 'id', 'addedField-' + formProcessor.nFields ).appendTo( '#formFields' );
};

formProcessor.up = function( sObj ) {
    var mainDiv = $( sObj ).parent( 'div' ).parent( 'div' );
    mainDiv.insertBefore( mainDiv.prev() );
};

formProcessor.down = function( sObj ) {
    var mainDiv = $( sObj ).parent( 'div' ).parent( 'div' );
    mainDiv.insertAfter( mainDiv.next() );
};

formProcessor.delete = function( sObj ) {
    var mainDiv = $( sObj ).parent( 'div' ).parent( 'div' );
    $( mainDiv ).remove();
};

formProcessor.turndown = function( divType ) {
    if( $( '#' + divType ).is( ':visible' ) ) {
        $( '#' + divType ).slideUp( 'fast' );
        $( '#' + divType + '-turner' ).attr( 'class', 'action up turner' );
    } else {
        $( '#' + divType ).slideDown( 'fast' );
        $( '#' + divType + '-turner' ).attr( 'class', 'action down turner' );
    }
};

formProcessor.makePreview = function() {

    $( '#formPreview' ).empty();
    $( '#formPreview' ).append( '<table id="previewTable"></table>' );

    if( !$( '#formFields' ).is( ':empty' ) ) {

        $( '#formFields > .formField' ).each( function( index ) {

            var fieldType = $( this ).find( 'input:hidden' ).val();
            var fieldId = $( this ).attr( 'id' );
            switch( fieldType ) {
                case 'text':
                        var fieldLabel = $( this ).find( 'input.fieldLabel' ).val();
                        var fieldDesc = $( this ).find( 'textarea.fieldDescription' ).val();
                        if( fieldLabel!='' ) {
                            $( '#formPreview table' ).append( '<tr original="' + fieldId +
                                    '"><td><label>' + fieldLabel + '</label><input type="text"/></td><td>' + fieldDesc + '</td></tr>' );
                        }
                    break;
                case 'textarea':
                    var fieldLabel = $( this ).find( 'input.fieldLabel' ).val();
                    var fieldDesc = $( this ).find( 'textarea.fieldDescription' ).val();
                    if( fieldLabel != '' ) {
                        $( '#formPreview table' ).append( '<tr original="' + fieldId + '"><td><label>' + fieldLabel + '</label><textarea/></td><td>' +
                                fieldDesc + '</td></tr>' );
                    }
                    break;
                case 'numeric':
                    var fieldLabel = $( this ).find( 'input.fieldLabel' ).val();
                    var fieldDesc = $( this ).find( 'textarea.fieldDescription' ).val();
                    if( fieldLabel != '' ) {
                        $( '#formPreview table' ).append( '<tr original="' + fieldId + '"><td><label>' + fieldLabel +
                                '</label><input type="number" value="0"/></td><td>' + fieldDesc + '</td></tr>' );
                    }
                    break;
                case 'checkbox':
                    var fieldLabel = $( this ).find( 'input.fieldLabel' ).val();
                    var fieldDesc = $( this ).find( 'textarea.fieldDescription' ).val();
                    if( fieldLabel != '' ) {
                        $( '#formPreview table' ).append( '<tr original="' + fieldId + '"><td><label><input type="checkbox" value="0"/>' + fieldLabel +
                                '</label></td><td>' + fieldDesc + '</td></tr>' );
                    }
                    break;
                case 'select':
                    var fieldLabel = $( this ).find( 'input.fieldLabel' ).val();
                    var fieldDesc = $( this ).find( 'textarea.fieldDescription' ).val();
                    var fieldVariants = $( this ).find( 'div.division textarea.selectVariants' ).val();
                    var variants = fieldVariants.split( ';' );

                    if( fieldLabel != '' && fieldVariants != '' && variants.length > 0 ) {
                        appendText = '<tr original="' + fieldId + '"><td><label>' + fieldLabel + ' </label>';
                        appendText = appendText + '<select>';

                        for( vi = 0; vi < variants.length; vi++ ) {
                            if( variants[vi] != '' ) {
                                appendText = appendText + '<option value="' + variants[vi] + '">' + variants[vi] + '</option>';
                            }
                        }

                        appendText = appendText + '</select>';
                        appendText = appendText + '</td><td>' + fieldDesc + '</td></tr>';

                        $( '#formPreview table' ).append( appendText );
                    }
                    break;
                case 'radio':
                    var fieldLabel = $( this ).find( 'input.fieldLabel' ).val();
                    var fieldDesc = $( this ).find( 'textarea.fieldDescription' ).val();
                    var fieldVariants = $( this ).find( 'div.division textarea.selectVariants' ).val();
                    var variants = fieldVariants.split( ';' );

                    if( fieldLabel != '' && fieldVariants != '' && variants.length > 0 ) {

                        var fakeNumber = Math.random( 9999 - 1000 ) + 1000;

                        appendText = '<tr original="' + fieldId + '"><td><label>' + fieldLabel + ' </label>';

                        for( vi = 0; vi < variants.length; vi++ ) {
                            if( variants[vi] != '' ) {
                                appendText = appendText + '<label><input type="radio" name="ftest_' + fakeNumber + '[]" />' + variants[vi] + '</label>';
                            }
                        }

                        appendText = appendText + '</td><td>' + fieldDesc + '</td></tr>';
                        $( '#formPreview table' ).append( appendText );
                    }
                    break;
            }
        } );

        $( '#previewTable tr' ).bind( 'mouseover', function ( ex ) {
            var originalFieldId = $( this ).attr( 'original' );
            $( '#' + originalFieldId ).addClass( 'fieldHover' );
        } );

        $( '#previewTable tr' ).bind( 'mouseleave', function() {
            $( '.formField' ).removeClass( 'fieldHover' );
        } );
    }
};

formProcessor.setFieldsCount = function( fieldCount ) {
    if( fieldCount>0 ) {
        formProcessor.nFields = fieldCount;
    }
};
