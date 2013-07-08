<?php

namespace Difra\Resourcer\Abstracts;

/**
 * Абстрактный класс работы с текстовыми ресурсами
 */
abstract class Plain extends Common {

	/**
	 * Сборка ресурсов в единую строку
	 * @param $instance
	 * @return string
	 */
	protected function processData( $instance ) {

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
			$this->resources[$instance]['files'] = array_reverse( $this->resources[$instance]['files'] );
			foreach( $this->resources[$instance]['files'] as $file ) {
				$result .= $this->getFile( $file );
			}
		}
		return $result;
	}

	private function getFile( $file ) {

		$debuggerEnabled = \Difra\Debugger::isEnabled();
		if( !$debuggerEnabled and !empty( $file['min'] ) ) {
			return file_get_contents( $file['min'] );
		} elseif( !$debuggerEnabled and !empty( $file['raw'] ) ) {
			return \Difra\Minify::getInstance( $this->type )->minify( file_get_contents( $file['raw'] ) );
		} elseif( $debuggerEnabled and !empty( $file['raw'] ) ) {
			return file_get_contents( $file['raw'] );
		} elseif( $debuggerEnabled and !empty( $file['min'] ) ) {
			return file_get_contents( $file['min'] );
		}
		return '';
	}
}
