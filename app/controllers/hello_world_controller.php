<?php

class HelloWorldController extends BaseController{
	public static function index(){
		View::make('thread_list.html');
	}

	public static function join() {
		View::make('join.html');
	}
}
