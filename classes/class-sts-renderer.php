<?php

class Sts_Renderer {

	public function render( $template, $data ) {

		if ( 0 < validate_file( $template ) ) {
			return '';
		}

		//@codingStandardsIgnoreLine
		extract( $data, EXTR_SKIP );
		ob_start();
		require $template;
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	public function templates_dir() {

		return STS_ROOT . 'templates/';
	}
}
