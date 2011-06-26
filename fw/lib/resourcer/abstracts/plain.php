<?php

namespace Difra\Resourcer\Abstracts;

abstract class Plain extends Common {
	
	protected function processData( $instance ) {
		
		$data = array();
		if( !empty( $this->resources[$instance]['specials'] ) ) {
			foreach( $this->resources[$instance]['specials'] as $resource ) {
				if( !empty( $resource['files'] ) ) {
					foreach( $resource['files'] as $file ) {
						$data[] = file_get_contents( $file );
					}
				}
			}
		}
		if( !empty( $this->resources[$instance]['files'] ) ) {
			foreach( $this->resources[$instance]['files'] as $file ) {
				$data[] = file_get_contents( $file );
			}
		}
		return implode( "\n", $data );
	}
}
