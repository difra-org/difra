<?php

chdir( dirname( __FILE__ ) . '/../..' );
$difra = new Phar( 'difra.phar' );
$difra->buildFromDirectory( '.', '/^\.\/(fw|plugins)\/*/' );
$difra->setStub( '<?php include("phar://".($_=__FILE__)."/fw/lib/bootstrap.php");__HALT_COMPILER();?>' );