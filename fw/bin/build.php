<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

chdir( __DIR__ . '/../..' );
$difra = new Phar( 'difra.phar' );
$difra->buildFromDirectory( '.', '/^\.\/(fw|plugins)\/*/' );
$difra->setStub( '<?php include("phar://".($_=__FILE__)."/fw/lib/bootstrap.php");__HALT_COMPILER();?>' );