<?php # -*- coding: utf-8 -*-

class Sts_Metabox {

	private $id;
	private $title;
	private $callback;
	private $screen;
	private $context;
	private $priority;
	private $callback_args;

	public function __construct( $id, $title, $callback, $screen = null, $context = 'advanced', $priority = 'default', $callback_args = null ) {

		$this->id            = $id;
		$this->title         = $title;
		$this->callback      = $callback;
		$this->screen        = $screen;
		$this->context       = $context;
		$this->priority      = $priority;
		$this->callback_args = $callback_args;
	}

	public function id() {

		return $this->id;
	}

	public function title() {

		return $this->title;
	}

	public function callback() {

		return $this->callback;
	}

	public function screen() {

		return $this->screen;
	}

	public function context() {

		return $this->context;
	}

	public function priority() {

		return $this->priority;
	}

	public function callback_args() {

		return $this->callback_args;
	}
}
