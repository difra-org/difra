<?php

namespace Difra\Resourcer\Abstracts;

abstract class Plain extends Common {
	
	protected function processData( $instance ) {

//		$t = microtime( true );
		$result = '';
		if( !empty( $this->resources[$instance]['specials'] ) ) {
			foreach( $this->resources[$instance]['specials'] as $resource ) {
				if( !empty( $resource['files'] ) ) {
					foreach( $resource['files'] as $file ) {
						$result .= $this->getFile( $file );
					}
				}
			}
		}
		if( !empty( $this->resources[$instance]['files'] ) ) {
			foreach( $this->resources[$instance]['files'] as $file ) {
				$result .= $this->getFile( $file );
			}
		}
//		echo "// " . ( microtime( true ) - $t ) . "\n";
		return $result;
	}

	private function getFile( $file ) {

		if( !\Difra\Debugger::getInstance()->isEnabled() ) {
			if( !empty( $file['min'] ) ) {
				return file_get_contents( $file['min'] );
			} elseif( !empty( $file['raw'] ) ) {
				return \Difra\Minify::getInstance( $this->type )->minify( file_get_contents( $file['raw'] ) );
			}
		} else {
			if( !empty( $file['raw'] ) ) {
				return file_get_contents( $file['raw'] );
			} elseif( !empty( $file['min'] ) ) {
				return file_get_contents( $file['min'] );
			}
		}
	}
}
