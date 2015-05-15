<?php

class CD_Widget extends WP_Widget {

	function __construct( $widget ) {

		parent::__construct(
			$widget['id'],
			$widget['title'],
			array( 'description' => $widget['description'] )
		);
	}

//	function form() {
//
//	}
//
//	function widget() {
//
//	}
}