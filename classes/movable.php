<?php
namespace Movable;

class Movable
{

	const key = 'movable';

	private static $key = null;

	public static function clear()
	{
		\Session::delete(static::generate_key());
	}

	public static function set($data)
	{
		foreach ($data as $key => $value)
		{
			$_POST[$key] = $value;
		}
	}

	public static function post($next = null, $prev_array = array())
	{
		static::check_prev($prev_array);
		static::regist_data(\Input::post());
		static::check_next($next);
	}

	public static function shift($next = null, $prev_array = array())
	{
		static::check_prev($prev_array);
		static::regist_data();
		static::check_next($next);
	}

	public static function replay($back)
	{
		\Response::redirect($back);
	}

	public static function restore()
	{
		$data_key = static::generate_data_key();
		$data = \Session::get($data_key);
		if (empty($data))
		{
			return;
		}
		foreach ($data as $key => $value)
		{
			$_POST[$key] = $value;
		}
	}

	private static function check_prev($prev_array = array())
	{
		$past_key = static::generate_past_key();
		foreach (array_reverse(array_keys($prev_array)) as $index)
		{
			$value = \Session::get($past_key.'.'.$prev_array[$index]);
			empty($value) and \Response::redirect($prev_array[$index]);
		}
	}

	private static function check_next($next = null)
	{
		\Input::method() == 'POST' and ! empty($next) and \Response::redirect($next);
	}

	private static function generate_key()
	{
		if ( ! empty(static::$key)) {
			return static::$key;
		}
		return static::key.'.'.\Request::active()->controller;
	}

	private static function generate_past_key()
	{
		return static::generate_key().'.past';
	}

	private static function generate_data_key()
	{
		return static::generate_key().'.data';
	}

	private static function regist_data($data = array())
	{
		$current = array(@$_SERVER['REQUEST_URI'] => 1);
		$past_key = static::generate_past_key();
		$past_array = \Session::get($past_key);
		$past_array = is_array($past_array) ? array_merge($past_array, $current) : $current;
		\Session::set($past_key, $past_array);
		$data_key = static::generate_data_key();
		foreach ($data as $key => $value)
		{
			\Session::set($data_key.'.'.$key, $value);
		}
	}

}