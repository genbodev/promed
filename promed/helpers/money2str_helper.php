<?php
/********************************************************************************************
*  Developed by J.R. (mail@jrlab.ru)
*  for KEngine library
*  NetExpert Company - netexpert.ru
*  class money2str
*  version 1.0.1
********************************************************************************************/
class num2str {
	var $nums = array(
		0 => '',
		1 => array( 'один', 'одна' ),
		2 => array( 'два', 'две' ),
		3 => 'три',
		4 => 'четыре',
		5 => 'пять',
		6 => 'шесть',
		7 => 'семь',
		8 => 'восемь',
		9 => 'девять',
		10 => 'десять',
		11 => 'одиннацать',
		12 => 'двенадцать',
		13 => 'тринадцать',
		14 => 'четырнадцать',
		15 => 'пятнадцать',
		16 => 'шестнадцать',
		17 => 'семнадцать',
		18 => 'восемнадцать',
		19 => 'девятнадцать',
		20 => 'двадцать', 
		30 => 'тридцать', 
		40 => 'сорок', 
		50 => 'пятьдесят', 
		60 => 'шестьдесят', 
		70 => 'семьдесят', 
		80 => 'восемьдесят', 
		90 => 'девяносто', 
		100 => 'сто', 
		200 => 'двести', 
		300 => 'триста', 
		400 => 'четыреста', 
		500 => 'пятьсот', 
		600 => 'шестьсот', 
		700 => 'семьсот', 
		800 => 'восемьсот', 
		900 => 'девятьсот'
	);
	
	var $names = array(
		1000 => array( 'тысяча', 'тысячи', 'тысяч', '', 'sem' => 1 ),
		1000000 => array( 'миллион', 'миллиона', 'миллионов', '', 'sem' => 0 ), 
		1000000000 => array( 'миллиард', 'миллиарда', 'миллиардов', '', 'sem' => 0 )
	);
	
	var $out = array();

	/**
	 * Функция
	 */
	function semantic( $num, $words )
	{
		$des = false;
		$num = $num % 100;
		if( $num > 20 )
		{
			$num = $num % 10;
			if( !$num ){
				$des= true;
			}
		}
		if ( 1 == $num ){
			return $words[0];
		}elseif( $des ){
			return $words[2];
		}elseif( !$num ){
			return $words[3];
		}elseif( $num <= 4  ){
			return $words[1];
		}else{
			return $words[2];
		}
	}

	/**
	 * Функция
	 */
	function small_nums( $num, $sem )
	{
		if( $num < 21 )
		{   
			if( $num <= 2 && isset($this->nums[$num][$sem]) )
			{
				$this->out[] =  $this->nums[$num][$sem];
			}else{
				//print $num." - ".$this->nums[$num]."<br/>";
				$this->out[] =  $this->nums[$num];
			}
		}else{
			$this->out[] = $this->nums[$num - ( $num % 10 )];
			if( ( $num % 10 ) <= 2 && isset($this->nums[$num % 10][$sem]) )
			{
				$this->out[] = $this->nums[$num % 10][$sem];
			}else{
				$this->out[] =  $this->nums[$num % 10];
			}
		}
	}

	/**
	 * Функция
	 */
	function work( $num, $all_sem )
	{
		foreach( array( 1000000000, 1000000, 1000 ) as $order )
		{
			$temp = floor( $num / $order );
			if( ( $temp - ( $temp % 100 ) ) > 0 ){
				$this->out[] = $this->nums[$temp - ( $temp % 100 )];
			}
			$this->small_nums( $temp % 100, $this->names[$order]['sem'] );
			$this->out[] = $this->semantic( $temp, $this->names[$order] );
			$num -= $temp * $order;
		}
		
		$temp = $num;
		if( ( $temp - ( $temp % 100 ) ) > 0 ){
			$this->out[] = $this->nums[$temp - ( $temp % 100 )];
		}
		$this->small_nums( $temp % 100, $all_sem );
		$temp = implode( ' ', $this->out );
		$this->out = array();
		return $temp;
		
	}
	
}


class money2str {
	var $names = array(
		1 => array( 'рубль', 'рубля', 'рублей', 'рублей', 'sem' => 0 ),
		2 => array( 'копейка', 'копейки', 'копеек', 'копеек', 'sem' => 1 )
	);
	var $out = array();

	/**
	 * Конструктор
	 */
	function __construct()
	{
		$this->num2str = new num2str();
	}

	/**
	 * Функция
	 */
	function work( $money, $kopnum = false )
	{
		$money = (string)($money);
		$kop = substr( $money, -2 );
		$rub = substr( $money, 0, -3 );

		if( !$rub )
		{
			$this->out[] = 'ноль';
		}else{
			$this->out[] = $this->num2str->work( $rub, $this->names[1]['sem'] );
		}
		$this->out[] = $this->num2str->semantic( $rub, $this->names[1] );
		if( !$kopnum )
		{
			if( !$kop )
			{
				$this->out[] = 'ноль';
			}else{
				if ( $kop == '0' || $kop == '00' )
					$this->out[] = 'ноль';
				else
					$this->out[] = $this->num2str->work( $kop, $this->names[2]['sem'] );
			}
		}else{
			$this->out[] = $kop;
		}
		$this->out[] = $this->num2str->semantic( $kop, $this->names[2] );
		$temp = implode( ' ', $this->out );
		$this->out = array();
		// обработка сотен
		$rub = str_replace(' ', '', $rub);
		if ( preg_match("/^[1-9]{1,1}00$/", substr($rub, 0, 3)) ) {
			preg_match("/^[1-9]{1,1}00$/", substr($rub, 0, 3), $arr);
			$num_text = $this->num2str->nums[$arr[0]];
			$n = strpos($temp, $this->num2str->nums[$arr[0]]);
			$cnt = 1;
			switch ( strlen($rub) )
			{
				case 6:
					$temp = substr_replace($temp, $num_text.' тысяч', 4, strlen($num_text));					
				break;
			}
		};
		return $temp;
	}
	
}
?>