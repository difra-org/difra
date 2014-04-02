/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

$( document ).on( 'change', '.plugins-toggle', function () {
	ajaxer.query( '/adm/development/plugins/' + ( this.checked ? 'enable' : 'disable' ) + '/' + this.name );
} );