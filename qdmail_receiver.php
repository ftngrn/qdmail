<?php
/**
 * QdmailReceiver including QdmailDecoder & QdPop
 * E-Mail for multibyte charset
 *
 * PHP versions 4 and 5 (PHP4.3 upper)
 *
 * Copyright 2008, Spok in japan , tokyo
 * hal456.net/qdmail    :  http://hal456.net/qdmail_rec/
 * & CPA-LAB/Technical  :  http://www.cpa-lab.com/tech/
 * Licensed under The MIT License License
 *
 * @copyright		Copyright 2008, Spok.
 * @link			http://hal456.net/qdmail_rec/
 * @version			0.1.4.alpha
 * @lastmodified	2008-09-15
 * @license			The MIT License http://www.opensource.org/licenses/mit-license.php
 * 
 * QdmailReceiver is POP Receive & decorde e-mail library for multibyte language ,
 * easy , quickly , usefull , and you can specify deeply the details.
 * Copyright (C) 2008   spok 
 * 
*/
//-----------------------------------------------------------------------------
// QdFunc
//-----------------------------------------------------------------------------
class QdmailReceiverFunc{

	//------------------------------------------------
	// $param : 'null' is returning present value,
	// $func : array( function name , line ) 
	// $type : array( type, Fatel or Not Fatal)
	// $property : propatey name 
	//------------------------------------------------
	function option( $param , $func = null , $type = null , $property = null){

		if(is_null($param) || (isset($func) && is_bool($func) && !$func)){
			return $this->{$property[0]};
		}
		if( is_numeric( $func ) ){
			$func = array( null , $func );
		}elseif( is_string( $func ) ){
			$func = array( $func , null );
		}
		if( is_null($func[0]) ){
			$func[0] = 'Unkwon Function';
		}elseif( is_null($func[1]) ){
			$func[1] = 'Unkwon Line Number';
		}
		$fg = false;
		switch($type[0]){
			case 'string':
				$fg = is_string( $param );
			break;
			case 'bool':
			case 'boolean':
				$fg = is_bool( $param );
			break;
			case 'numeric':
				if( isset( $type[1] ) ){
					$fg = ( $type[1] < $param ) ;
				}
				if( isset( $type[2] ) ){
					$fg = ( $type[2] > $param ) ;
				}
			break;
			default:
			break;
		}

		if($fg){
			$this->{$property[0]} = $param;
			return true;
		}else{
			return $this->error('Parameter Specified Error, Function name: ' . $func[0] , $func[1] );
		}
		return false;
	}

	function arrayDigup( $array , $keys ){
		$key = array_shift( $keys );
		if( isset( $array[$key] ) ){
			if( 0 === count( $keys ) ){
				return $array[$key];
			}else{
				return $this->arrayDigup( $array[$key] , $keys );
			}
		}elseif( isset( $array[0] )){
				array_unshift( $keys , $key );
				return $this->arrayDigup( $array[0] ,  $keys );
		}else{
			return false;
		}
	}

}
//------------------------------------------------------------
// QdmailReceiverDebug
//------------------------------------------------------------
class QdmailReceiverDebug extends QdmailReceiverFunc{

	var $debug				= 0; //degub level
	var $debug_echo_charset = 'utf-8';
	var $LFC				= "\r\n";
	var $log_LFC			= "\r\n";
	//-------------------------------
	// Debug
	//-------------------------------
	function debug( $level=null ){
		if( is_null( $level ) || !is_numeric($level) ){
			return $this->debug;
		}
		$this->debug = $level ;
		return true;
	}
	function debugEchoLine(){
		$vars = func_get_args();
		$this->debugEcho( false , $vars );
	}
	function debugEchoLf(){
		$vars = func_get_args();
		$this->debugEcho( true , $vars );
	}
	function debugEcho( $lf , $vars = null ){

		static $already_header = false;
		static $already_footer = false;
		if( 1 > $this->debug ){
			return;
		}
		if( !$already_header ){
			$head='<html><head><meta http-equiv="content-type" content="text/html; charset='.$this->debug_echo_charset.'"></head><body>';
			echo $head ;
			$already_header = true ;
		}
		if( $already_header && ( 'END' === $lf ) && !$already_footer){
			$foot ='</body></html>';
			echo $foot;
			$already_footer = true;
			return ;
		}
		$out = null;
		if( !is_array( $vars ) ){
			$vars =array( $vars );
		}
		foreach($vars as $var){
			$_out = print_r( $var , true ) ;
			$enc = mb_detect_encoding( $_out );
			if( strtoupper( $this->debug_echo_charset ) !== strtoupper( $enc ) ){
				$_out = mb_convert_encoding( $_out , $this->debug_echo_charset , $enc );
			}
			$out .=  $_out  . $this->LFC;
		}
		$spacer = $this->log_LFC ;
		if( !$lf ){
			$out = preg_replace("/\\r?\\n/",' ',$out);
			$spacer = null ;
		}

		echo "<pre>";
		$out = htmlspecialchars( $this->name . ' Debug: ' . $spacer . trim( $out ) , ENT_QUOTES , $this->debug_echo_charset );
		echo  $out;
		echo "</pre>";
	}
}
//----------------------------------------------------------------------------
// QdmailReceiverError
//----------------------------------------------------------------------------
class QdmailReceiverError extends QdmailReceiverDebug{

	var $error		=array();
	var $noteice	=array();
	var $error_display = true;
	var $error_fatal_ignore = false;

	function error( $message = null, $line = null , $fatal = false){
		if( is_null( $message ) ){
			return $this->error;
		}
		if(!is_null($line)){
			$message .= ' line: ' . $line;
		}
		if($this->error_display){
			echo '<br>'.get_class($this).': '.$message;
		}
		$this->error[] = $message;
		if( $fatal && !$this->error_fatal_ignore ){
			die( "\r\n".get_class($this) . ' Fatal Error line: ' .__LINE__);
		}
		return false;
	}
	function errorFatal( $message , $line = null , $fatal = false ){
		return $this->error( $message , $line , !$this->error_fatal_ignore );
	}
	function errorDisplay( $param = null ){
		return $this->option($param,array('errorDisplay',__LINE__),array('bool'),array('error_display'));
	}
	function errorFatalIgnore( $param = null ){
		return $this->option($param,array('errorFatalIgnore',__LINE__),array('bool'),array('error_fatal_ignore'));
	}
}
//-----------------------------------------------------------------------------
// QdDecodeBase
//-----------------------------------------------------------------------------
class QdDecodeBase extends QdmailReceiverError{

	var $name			= 'QdDecodeBase';
	var $version			= '0.1.4.alpha';
	var $x_licese			= 'The_MIT_License';
	var $x_url				= 'http://hal456.net/qdmail_rec/';

	var $target_charset	= null;
	var $header_all		= null;
	var $headr			= array();
	var $header_name	= array();
	var $body_all		= null;
	var $all			= null;
	var $body			= array();
	var $attach			= array();
	var $done			= false;
	var $line			= array();
	var $num			= 0;
	var $max			= 0;
	var $text_decode	= true;// including html
	var $is_html			= null;
	var $attach_decode	= true;
	var $already_header	= false;
	var $already_text	= false;// including html
	var $already_attach	= false;
	var $already_getMail= false;
	var $other_multipart = array(
		'alternative'		=>'skip',
		'related'			=>'skip',
		'signed'			=>'skip',
		'mixed'				=>'skip',
		'x-mixed-replace'	=>'skip',
		'parallel'			=>'skip',
		'encrypted'			=>'skip',
	);

	//--------------
	// Constructor 
	//--------------
	function __construct( $param = null ){
		$this->QdDecodeBase( $param );
	}
	function QdDecodeBase( $param = null ){

		$enc = mb_internal_encoding();
		if( !empty( $enc ) ){
			$this->charset( $enc );
		}

		if( isset( $param[1] ) && is_string( $param[1] )){
			$this->target_charset = $param[1];
		}
		if( isset( $param['name'] ) ){
			$this->name = $param['name'];
		}

	}

	//***********************************************************
	//**   For OverRide                                        **
	//***********************************************************
	function getMail(){
		$this->_before_getMail();
		$this->_after_getMail();
	}
	function buildHeader( $header_laof ){
		return $this->_before_buildHeader( $header_laof );
	}
	//-------------------------------
	// Get(Set) One Mail
	//-------------------------------
	function _before_getMail(){
			$this->alreadyReset();
	}
	function _after_getMail(){
			$this -> already_getMail = true;
	}
	//-----------------------------------------------------------------------
	// Option Specify
	//$this->option(
	//	$param,						// parameter
	//	array('charset',__LINE__),	// MyFunction Name & line for ErrorCode
	//	array('string'),			// Type , 'false' means ReadOnly
	//	array('target_charset')		// My propatey_name for change
	//	);
	//-----------------------------------------------------------------------
	function charset( $param = null ){
		return $this->option($param,array('charset',__LINE__),array('string'),array('target_charset'));
	}
	function headerAll( $param = null ){
		return $this->option($param,array('headerAll',__LINE__),false,array('header'));
	}
	// all eq set
	function all( $param = null ){
		$ret = $this->option($param,array('all',__LINE__),array('string'),array('all'));
		if( !is_null( $param ) && false!==$ret && !empty( $ret ) ){
			$name = isset( $this->name ) ? $this->name : get_class( $this );
			$ret = $this->all = 'X-' . $name . ': ' . 'version'.$this->version .'-'. $this->x_licese . ' ' . $this->x_url . "\r\n" . ltrim( $this->all ) ;
		}
		return $ret;
	}
	function set( $param = null ){
		return $this->all( $param );
	}
	function bodyDecode( $param = null ){
		return $this->option($param,array('textDecode',__LINE__),array('bool'),array('text_decode'));
	}
	function attachDecode( $param = null ){
		return $this->option($param,array('textDecode',__LINE__),array('bool'),array('attach_decode'));
	}
	function header( $param = null , $return_false = false ){

		if( !$this->already_header ){
			$this->decodeHeader();
		}
		if(is_null($param)){
			return $this->header;
		}
		if( is_string($param) && ( 'all' === strtolower($param) )){
			return $this->headerAll();
		}
		if(is_string($param)){
			$param = array( $param );
		}
		$ret = $this->arrayDigup( $this->header , $param ) ;
		return ( false === $ret ) ? $return_false:$ret;
	}
	function headerName( $param = null ){
		return $this->option($param,array('headerName',__LINE__) , false , array('header_name'));
	}
	function bodyFull( $param = null ){
		if( !$this->already_text || !$this->already_attach ){
			$this->decodeBody();
		}
		return array_merge( $this->body() , array( 'attach' => $this->attach( $param = null ) ) );
	}
	function body( $param = null ){
		if( !$this->already_text ){
			$this->decodeBody();
		}
		if( !is_array( $param ) ){
			$param = array( $param );
		}
		$ret = $this->option(null,array( 'body' ,__LINE__) , false , array('body') );
		$ret = $this->arrayDigup( $ret , $param ) ;
		return $ret;
	}
	function bodyAutoSelect(){
		$ret = $this->body(array('html','value'));
		if( !empty( $ret ) ){
			$this->is_html = true;
			return $ret;
		}
		$ret = $this->body(array('text','value'));
		if( !empty( $ret ) ){
			$this->is_html = false;
			return $ret;
		}
		return false;
	}
	function isHtml(){
		if( !isset( $this->is_html ) ){
			if( false === $this->bodyAutoSelect() ){
				return false;
			}
		}
		return $this->option( null ,array('isHtml',__LINE__),false,array('is_html'));
	}

	function text(){
		return $this->body(array('text','value'));
	}
	function html(){
		return $this->body(array('html','value'));
	}
	function attach( $param = null ){
		if( !$this->already_attach ){
			$this->decodeBody();
		}
		return $this->option($param,array('attach',__LINE__) , false , array('attach') );
	}
	function version(){
		return $this->version;
	}
	//---------------------------------------
	// Parameter settings & analysis
	//---------------------------------------
	function alreadyReset(){
		$this->already_header = false;
		$this->already_text   = false;
		$this->already_attach = false;
		$this -> already_getMail = false;
		$this->header_all	= null;
		$this->body_all		= null;
		$this->header		= array();
		$this->header_name	= array();
		$this->body			= array();
		$this->attach		=array();
		return true;
	}
	function decodeHeader(){
		static $addr = array(
			'to',
			'cc',
			'bcc',
			'reply-to',
			'from',
		);

		if(	!$this -> already_getMail ){
			$this->getMail();
		}

		// cutting
		if( 0 === preg_match( '/\r?\n\r?\n/is', trim( $this->all ), $matches, PREG_OFFSET_CAPTURE)){
			$this->header_all = $this->all ;
			$this->body_all   = null;
		}else{
			$offset = $matches[0][1] ;
			$this->header_all = trim( substr( $this->all , 0 , $offset ) ) ;
			$this->body_all   = trim( substr( $this->all , $offset + 1 ) ) ;
		}
		$this->header = $this->buildHeader( $this->header_all );
		// address field action , force to array type
		foreach( $addr as $ad ){
			if( !isset( $this->header[$ad]) ){ continue; }
			if(is_array($this->header[$ad])){
				$addr_header = array_shift( $this->header[$ad] );
			}else{
				$addr_header = $this->header[$ad];
			}
			$person = explode( ',' , $addr_header );
			$this->header[$ad] = array();
			foreach($person as $pers){
				if( !empty( $pers ) ){
					$this->header[$ad][] = $this->splitMime( $pers , true );
				}
			}
		}
		// subject
		if( isset( $this->header['subject'] ) ){
			$this->header['subject']= $this->splitMime( $this->header['subject'] , false );
		}
		$this->already_header = true;
	}

	function decodeBody(){
		if( !$this->already_header ){
			$this->decodeHeader();
		}
		// body
		if( ( !$this->already_text && $this->text_decode )
				||
			( !$this->already_attach && $this->attach_decode ) ){
			if( isset( $this->header['content-type'] ) ){
				$type = $this->typeJudge( $this->header['content-type'] );
				preg_match( '/boundary\s*=\s*"([^"]+)"/is' , $this->header['content-type'] , $matches );
				if( isset( $matches[1] ) ){
					$this->line = preg_split( '/\r?\n/is' , $this->body_all );
					$this->num = 0;
					$this->max = count( $this->line );
					$this->buildPart( $matches[1] , $type );
				}else{
					$this->body[$type] = $this->makeBody( $this->header , $this->body_all );
					$this->already_text = true;
				}
			}else{
				$type ='unknown';
				$_hd = array( 'content-type' => $type . '/' . $type );
				$this->body[$type] = $this->makeBody( array_merge( $this->header , $_hd ) , $this->body_all );
			}
		}
	}
	//----------------------------
	// Header Build And Make
	//----------------------------
	function _before_buildHeader( $header_laof ){
		$header = array();
		$line = preg_split( '/\r?\n/is' , trim( $header_laof ) );
		// connect line
		$prev_key = 0;
		foreach($line as $key => $li){
			if(1 === preg_match('/^\s/',$li)){
				$line[$key] = $line[$prev_key] ."\r\n". $line[$key];
				unset( $line[$prev_key] );
			}
			$prev_key = $key;
		}
		// split header
		foreach($line as $li){
			if( false !== ( $split = strpos( $li , ':' ) ) ){
				$obj = trim( substr( $li , $split+1 ) );
				$p_name = strtolower( $org = substr( $li , 0 , $split ) );
				if( isset($header[$p_name]) && !is_array($header[$p_name]) ){
					$temp = $header[$p_name];
					$header[$p_name] = array( $temp , $obj ) ;
				}elseif( isset($header[$p_name]) && is_array($header[$p_name]) ){
					$header[$p_name][] = $obj;
				}else{
					$header[$p_name] = $obj;
					$this->header_name[$p_name] = $org ;
				}
			}else{
				continue;
			}
		}
		return $header;
	}
	//--------------------------------------
	// Multipart
	//--------------------------------------
	function buildPart( $boundary , $type){
		$body = array();
		if( !$this->skipto( '--' . $boundary ) ){
			return false;
		}
		do{
			// header in body
			$header = null;
			do{
				$li = $this->get_1_line( false );
				if( false === $li ){
					return false;
				}
				$header .= $li . "\r\n" ;
			}while( !empty( $li ) || '0' === $li );

			$header = $this->buildHeader( $header );

			if( isset( $header['content-type'] ) ){
				$type = $this->typeJudge( $header['content-type'] );
				preg_match( '/boundary\s*=\s*"([^"]+)"/is' , $header['content-type'] , $matches );
				if( !empty( $matches[1] ) ){
					$this->buildPart( $matches[1] , $type );
				}
			}else{
				$type = 'unknown';
				$header['content-type'] = $type . '/' . $type;
			}
			// ||<
			if( ( !$this->attach_decode && ( $type == 'attach' || $type == 'unknown' ) )
					 ||
			    ( !$this->text_decode && ( $type == 'text' || $type == 'html' ) )
			){

				if( !$this->skipto('--'.$boundary) ){
					return false;
				}
					continue ;
			}

			$plain_body = null;
			$li = $this->get_1_line(false);
			while( ( trim( $li ) != '--'.$boundary.'--') && ( trim($li) != '--'.$boundary) && (false !== $li ) ){
				$plain_body .= $li . "\r\n" ;
				$li = $this->get_1_line( false );
			}
			$_body = $this->makeBody( $header , $plain_body );
			if( $_body['attach_flag'] ){
				$type = 'attach';
			}

			if(  $type == 'attach' || $type == 'unknown'){
				$this->attach[] = $_body ;
				$this->already_attach = true ;
			}elseif(  $type == 'text' || $type == 'html'){
				$this->body[$type] = $_body ;
				$this->already_text = true ;
			}//else $type === 'skip'

		}while( trim( $li ) == '--'.$boundary );
	return true;
	}
	//----------------------------------------
	// Decode MimePart and set to Result Array
	//----------------------------------------
	function makeBody( $header , $body ){

		if(1===preg_match('/charset\s*=\s*"?([^\s;"]+)"?\s*;?\r?\n?/is',$header['content-type'],$matches)){
			$charset = $matches[1];
		}

		$encoding = isset($header['content-transfer-encoding']) ? $header['content-transfer-encoding'] : '7bit' ;

		if( !is_null( $body ) && ( 'base64' === strtolower( $encoding ) ) ){
			$body = base64_decode( $body );
		}elseif( !is_null( $body ) && ( 'quoted-printable' === strtolower( $encoding ) ) ){
			$body = quoted_printable_decode( $body );
		}

		if( !is_null( $body ) && ( 1===preg_match('/text\//is' , $header['content-type'] , $matches ) ) ){
			$charset = isset($charset) ? $charset : mb_detect_encoding( $body );

//			mb_check_encoding ([ string $var [, string $encoding ]] )
			if( false !== strpos( strtoupper( $charset ) , 'UNKNOWN' ) ){
				$charset = mb_detect_encoding($body);
			}
			$stack = mb_detect_order();
			if( ( false !== mb_detect_order( $charset ) ) && isset( $this->target_charset ) && ( strtolower($this->target_charset) != strtolower($charset))){
				if( mb_check_encoding( $body , $charset ) ){
					$body = mb_convert_encoding( $body , $this->target_charset , $charset );
				}
			}
			mb_detect_order($stack);
		}
		
		$ret = array();
		if( isset( $charset ) ){
			$ret['charset'] = $charset;
		}
		// attachment or other
		$ret['attach_flag'] = false;
		if( !empty( $header['content-disposition'] ) || !empty( $header['content-id'] ) ){
				$ret['attach_flag'] = true;
		}
		if( !empty( $header['content-id'] ) ){
				$ret['content-id'] = $header['content-id'];
				$ret['content-id_essence'] = trim(trim($header['content-id'],'<>'));
		}

		// filename
		$filename = '';
		if( $ret['attach_flag'] && (1===preg_match('/name\s*=\s*"?([^"\s\r\n]+)"?\r?\n?/is',$header['content-type'] , $matches ) ) ){
			$filename = $matches[1];
		}elseif($ret['attach_flag'] && isset($header['content-disposition']) && (1===preg_match('/name\s*=\s*"?([^"\s\r\n]+)"?\r?\n?/is' , $header['content-disposition'] , $matches ) ) ){
			$filename = $matches[1];
		}
		if( 1 === preg_match( '/(=\?.+\?=)/is ' , $filename , $matches ) ){
			$_filename = $this->qd_decode_mime($matches[1]);
			$org_charset = mb_internal_encoding();
			if( isset( $this->target_charset ) && ( strtolower($this->target_charset) != strtolower($org_charset))){
				if( mb_check_encoding( $_filename , $org_charset ) ){
					$_filename = mb_convert_encoding(
						$_filename ,
						$this->target_charset,
						$org_charset
					);
				}
			}

			$filename = str_replace( $matches[1] , $_filename , $filename );
		}

		$filename = trim($filename);
		if( !empty( $filename ) ){
			$ret['filename'] = $filename;
			$ret['filename_safe'] = urlencode($filename);
		}

		//mimetype
		if( 1 === preg_match('/^\s*([^\s]*\/[^\s;]+)/is' , $header['content-type'] , $matches ) ){
			$ret['mimetype'] = $matches[1] ;
		}

		$ret['enc'] = $encoding;
		$ret['content-type'] = $header['content-type'];
		$ret['value'] = $body;
		return $ret;
	}
	//-------------------
	// Mimetype Judge
	//-------------------
	function typeJudge( $value ){
		preg_match('/\s*([^\s;,]+\/[^\s;,]+)\s*;?/is',$value,$matches);
		if( !empty( $matches[1] ) && 'TEXT/PLAIN'==strtoupper($matches[1])){
			$type = 'text';
		}elseif( !empty( $matches[1] ) && 'TEXT/HTML'==strtoupper($matches[1])){
			$type = 'html';
		}elseif( !empty( $matches[1] ) ){
			$slash = strpos( $matches[1] , '/' );
			if( false !==$slash ){
				$mime_main = strtolower( substr( $matches[1] , 0 , $slash ) );
				$mime_sub  = strtolower( substr( $matches[1] , $slash+1 ) );
				if( 'multipart' === $mime_main ){
					if(isset($this->other_multipart[$mime_sub]) && 'skip'===$this->other_multipart[$mime_sub]){
						$type = 'skip';
					}else{
						$type = 'unknown';
					}
				}else{
					$type = 'attach';
				}
			}else{
				$type = 'attach';
			}
		}else{
				$type = 'attach';
		}
		return $type;
	}
	//---------------------
	// Step
	//---------------------
	function skipto( $boundary ){
		$fg = true;
		while( trim( $this->line[$this->num] ) != trim( $boundary )  ){
			$this->num++;
			if( $this->num >= $this->max ){
				$fg = false;
				break;
			}
		}
		return $fg;
	}

	function get_1_line( $empty = true ){
		$fg = true;
		do{
			if( !isset( $this->line[$this->num] ) ){
				$fg = false;
				break;
			}
			$li = rtrim( $this->line[$this->num++] );
			if( $this->num >= $this->max ){
				$fg = false;
				break;
			}
		}while( $empty && empty($li) );
		return $fg ? $li:false;
	}

	//------------------
	// Mime Decode
	//------------------
	function qd_decode_mime( $string ){
		$ret = mb_decode_mimeheader($string);
		return $ret;
	}
	function splitMime( $var , $address_mode = false){
		$obj = array();
		$obj['value'] = trim( $var );

		$var = preg_replace( '/\r?\n\s*=\?/' ,'=?',$var );
		preg_match_all('/=\?(?:(?!\?=).)*\?=/is' , $var , $matches);

		$mime_fg =false;
		if( 0 < count($matches[0]) ){
			$rep = array();
			foreach($matches[0] as $one){
				$rep[] = $this->qd_decode_mime( $one );
			}
			$var = str_replace($matches[0],$rep,$var);
			$mime_fg = true;
		}

		if( $address_mode && ( 1 === preg_match( '/<?([^<>\s]+@[^<>\s]+\.[^<>\s]+)>?/is' , $var , $matches ) ) ){
			$obj['mail'] = trim( $matches[1] );
			$obj['name'] = trim( trim( str_replace( $matches[0] , '' , $var ),"\" \t") );
			if(empty($obj['name'])){
				unset($obj['name']);
			}
		}elseif(!$address_mode){
			$obj['name'] = $var;
		}

		if( $mime_fg && !empty($obj['name']) ){
			$org_charset = mb_internal_encoding();
			$org_charset = empty($org_charset) ? null:$org_charset;
			if( isset( $this->target_charset ) && ( strtolower($this->target_charset) != strtolower($org_charset))){
				$obj['name'] = trim(mb_convert_encoding(
					$obj['name'],
					$this->target_charset,
					$org_charset
				));
			}
		}
		return $obj;
	}
}
//-------------------------
// Normal Qddecode
//-------------------------
class QdDecode extends QdDecodeBase{

	var $name ='QdDecode';

	function QdDecode( $param = null ){
		if( !is_null( $param ) ){
			$param = func_get_args();
		}
		parent::__construct( $param );
	}
}

/**
 * QdPop ver 0.1.4a
 * POP Receiver for PHP
 *
 * PHP versions 4 and 5 (PHP4.3 upper)
 *
 * Copyright 2008, Spok in japan , tokyo
 * hal456.net/qdmail_decode : http://hal456.net/qdmail_rec/
 * & CPA-LAB/Technical      : http://www.cpa-lab.com/tech/
 * Licensed under The MIT License License
 *
 * @copyright		Copyright 2008, Spok.
 * @link			http://hal456.net/qdmail_rec/
 * @version			0.1.4alafa
 * @lastmodified	2008-09-15
 * @license			The MIT License http://www.opensource.org/licenses/mit-license.php
 * 
 * Qdmail is sending e-mail library for multibyte language ,
 * easy , quickly , usefull , and you can specify deeply the details.
 * Copyright (C) 2008   spok 
*/

class QdPopBase extends QdDecodeBase{

	var $name				= 'QdPopBase';

	var $server = array(
		'host'=>'',
		'port'=>110,
		'user'=>'',
		'pass'=>'',
	);
	var $time_out	= 5;
	var $pointer	= 1;
	var $count		= 0;
	var $delete		= false;
	var $fp			= null;
	var $pop_high_speed = -1;
	var $get_uid	= true;
	var $popuid		= 'popuid';
	var $uid_list	= array();

	//--------------------------
	// Constructor
	//--------------------------
	function __construct( $param = null ){
		$this->QdPopBase($param);
	}
	function QdPopBase( $server ){
		if( isset( $server[0] ) ){
			$this->server = array_merge( $this->server , $server[0] );
		}
		parent::__construct( $server );
	}

	function buildHeader( $header_laof ){

		$header = $this->_before_buildHeader( $header_laof );

		if( $this->get_uid ){
			$i = 0;
			$key = $this->popuid;
			while( isset($header[$key]) && $i < 10000 ){
				$key = $this->popuid.'_'.$i++;
			}
			$id = $this->getUid() ;
			if( false !== $id ){
				$header[$key] = $id;
			}
		}
		return $header;
	}

	//-----------------------------------------------------------------------
	// Option Specify
	//$this->option(
	//	$param,						// parameter
	//	array('charset',__LINE__),	// MyFunction Name & line for ErrorCode
	//	array('string'),			// Type , 'false' means ReadOnly
	//	array('target_charset')		// My propatey_name for change
	//	);
	//-----------------------------------------------------------------------
	function popHigh( $param = null ){
		return $this->option($param,array('popHigh',__LINE__),array('numeric',-1),array('pop_high_speed'));
	}
	function popUid( $param = null ){
		return $this->option($param,array('popUid',__LINE__),array('bool'),array('get_uid'));
	}
	function popUidAll(){
		if( empty( $this->uid_list ) ){
			$fg = $this->getUidAll();
			if( !$fg ){
				return false;
			}
		}
		return $this->option( null ,array('popUidAll',__LINE__),false,array('uid_list'));
	}
	function timeout( $param = null ){
		$fg = $this->option($param,array('timeout',__LINE__),array('numeric',0),array('time_out'));
		if( is_resource( $this->fp ) && is_numeric( $this->time_out ) ){
			stream_set_timeout ( $this->fp , $this->time_out ) ;
		}
		return $fg;
	}
	function deleteAlways( $param = null ){
		return $this->option($param,array('deleteAlways',__LINE__),array('bool'),array('delete'));
	}
	//----------------------------------
	// make Connection and close
	//----------------------------------
	function connect(){
		$this->fp=fsockopen($this->server['host'],$this->server['port'], $err , $errst , $this->time_out );
		if(!is_resource($this->fp)){
			return $this->errorFatal('Connection Failure \''.$this->server['host'].'\' Port \''.$this->server['port'].'\'',__LINE__);
		}
		stream_set_timeout ( $this->fp , $this->time_out );
		$this->getMessage( true );
		list($fg1,$void)=$this->communicate('USER '.$this->server['user'],array('USER ID Error',__LINE__,!$this->error_fatal_ignore),true);
		list($fg2,$void)=$this->communicate('PASS '.$this->server['pass'],array('PASS ID Error',__LINE__,!$this->error_fatal_ignore),true);

		$this->uid_list	=array();
		return  $fg1 && $fg2 ;
	}
	function close(){
		$_ret = $this->cmd('QUIT',null,true);
		return fclose($this->fp) && $_ret;
	}
	function done(){
		return $this->close();
	}

	//----------------------------------
	// mail operation
	//----------------------------------
	function preCheck(){
		if(!is_resource($this->fp)){
			return $this->connect();
		}
		return true;
	}
	function count( $retry = false ){

		if(!$retry && $this->count > 0 ){
			return $this->count;
		}

		$this->preCheck();
		list($_ret) = $this->cmd('STAT',null,true);
		$ret = explode(' ',$_ret);
		$this->count = $ret[1];
		return $this->count;
	}

	function getMailAll(){
		$this->preCheck();
		( 0 !== $this->count ) or $this->count();
		$mail =array();
		for($i = 1 ; $i <= $this->count ; $i ++){
			$this->getMail();
		}
		return $mail;
	}
	// OverRide Parent by POP
	function getMail(){

		$this->_before_getMail();

		if( -1 !== $this->pop_high_speed ){
			$all = $this->getTop( null , $this->pop_high_speed );
		}else{
			$this->preCheck();
			( 0 !== $this->count ) or $this->count();
			if( ( 0 >= $this->pointer) || ( $this->pointer > $this->count )  ){
			return false;
		}
			$mail = $this->cmd('RETR', $this->pointer , false , false );
			!$this->delete or $this->delete($i);
			$all = $mail[1];
		}

		$this->set($all);
		$this->_after_getMail();
		return $all;
	}

	function getTop( $msg_num = null , $line_num = null ){
		$this->preCheck();
		( 0 !== $this->count ) or $this->count();
		if( ( 0 >= $this->pointer) || ( $this->pointer > $this->count )  ){
			return false;
		}
		$msg_num = is_null( $msg_num ) ? $this->pointer : $msg_num;
		$line_num = is_null( $line_num ) ? 0 : $line_num;
		$mail = $this->cmd( 'TOP' , $msg_num . ' ' . $line_num , false , false );
		return $mail[1];
	}

	function getUid( $msg_num = null ){

		$this->preCheck();
		( 0 !== $this->count ) or $this->count();
		if( ( 0 >= $this->pointer) || ( $this->pointer > $this->count )  ){
			return false;
		}
		$msg_num = is_null( $msg_num ) ? $this->pointer : $msg_num;
		$mail = $this->cmd( 'UIDL' , $msg_num , true , false );
		$_id = explode(' ',$mail[0]);
		if( '+OK' === strtoupper( trim( $_id[0] ) ) ){
			$num = $_id[1];
			$id  = $_id[2];
		}else{
			$num = $msg_num;
			$id  = null;
		}
		if( ( (int) $num != (int) $msg_num ) || empty($id) ){
			return false;
		}
		return $id;
	}

	function getUidAll(){
		$this->preCheck();
		$mail = $this->cmd( 'UIDL' , null , false , true );
		$_id = explode(' ',array_shift($mail));
		if( '+OK' === strtoupper( trim( $_id[0] ) ) && 0 < count($mail) ){
			foreach($mail as $ma){
				if( '.' === $ma ){  // fool proof
					break;
				}
				$temp = explode(' ',$ma);
				$this->uid_list[$temp[0]] = trim( $temp[1] );
			}
			return true;
		}else{
			return false;
		}
	}

	function uidToNum( $uid ){
		if( 0 === count( $this->uid_list ) ){
			$this->getUidAll();
		}
		return array_search( $uid , $this->uid_list) ;
	}


	function reset( $msg_num = null , $line_num = null ){
		$this->preCheck();
		$mail = $this->cmd( 'RSET' , true , false );
		return $mail[0];
	}
	function next(){
		$this->alreadyReset();
		return $this->pointer('++');
	}
	function prev(){
		$this->alreadyReset();
		return $this->pointer('--');
	}
	function pointer( $param = null ){
		$this->alreadyReset();
		if(is_null($param)){
			return $this->pointer;
		}
		if(is_numeric($param)){
			$this->pointer = $param ;
		}elseif('++'===$param){
			$this->pointer++;
		}elseif('--'===$param){
			$this->pointer--;
		}else{
			return false;
		}
		if( ( 0 >= $this->pointer) || ( $this->pointer > $this->count )  ){
			return false;
		}
		return true;
	}

	function delete( $num = null , $done = false ){
		$num = is_null($num) ? $this->pointer : $num ;
		if(!is_numeric($num)){
			$this->error( 'Specifed Error , delete command needs numeric' , __LINE__ );
		}

		$ret1 = !( false === $this->cmd('DELE', $num , true ) );
		$ret2 = true;
		if( $done ){
			$ret2 = $this->done();
		}

		return $ret1 && $ret2;
	}

	function deleteUid( $uid ){
		if( !is_array($uid) ){
			$uid = array( $uid );
		}
		$fg = true;
		$this->pointer(1);
		$uid = array_flip( $uid );
		$max = $this->count();
		for($i = 1 ; $i <= $max ; $i++ ){
			$key=$this->getUid($i);
			if( isset($uid[trim($key)]) ){
				$fg = $this->delete( $i ) && $fg;
			}
		}
		return ;
	}

	function listHeader(){

		$parameter = func_get_args();

		foreach($parameter as $key => $param){
			if(is_string($param)){
				$parameter[$key] = array( $param );
			}
		}

		$ret = array();
		$max = $this->count();
		$stack = $this->popHigh();
		$this->popHigh(0);
		for( $i = 1 ; $i <= $max ; $i ++ ){
			$this->pointer($i);
			foreach($parameter as $key => $param){
				$ret[$i][$param[0]] = $this->header( $param , null );
			}
		}
 	$this->popHigh($stack);
	return $ret;

	}

	function eof(){
		$ct = $this->count();
		$pt = $this->pointer();
		return  empty( $ct ) || empty( $pt ) || ( $pt > $ct ) ;
	}

	//--------------------------------------------
	// for Communication to POP Server
	//--------------------------------------------
	function cmd( $cmd , $param=null , $line_1=false , $array = true ){
		$cmd .= isset($param) ? ' '.$param:null;
		list( $fg , $ret ) = $this->communicate( $cmd , array('Error Command '.$cmd,__LINE__,true) , $line_1,$array);

		if(false===$fg){
			$ret = array( false , false );
		}

		return $ret;
	}

	function communicate( $put_message , $err = null , $line_1 = false , $array=true){
		if(!$this->preCheck()){
			return false;
		}

		$this->debugEchoLine('Client: ',$put_message);
		fputs( $this->fp , $put_message."\r\n");
		$ret = $this->getMessage($line_1 , $array );
		if( '+OK' === strtoupper( substr( $ret[0] , 0 , 3 ) ) ){
			$fg = true;
		}else{
			$er = print_r(stream_get_meta_data ( $this->fp ) , true );
			$this->error( 'Comunicate Error, stream_get_meta_data=>'.$er ,__LINE__);
			if(isset($err)){
				$this->error( $err[0] , isset($err[1]) ? $err[1]:null , isset($err[2]) ? $err[2]:null );
			}
			$fg = false;
		}
		return array( $fg , $ret );
	}

	function getMessage( $line_1 = false , $array = true){

		if(!$this->preCheck()){
			return false;
		}
		$r = fgets( $this->fp , 512 );
		$this->debugEchoLine('Server: ',$r);


		if( ('.' === substr( $r , -1 )) || $line_1 ) {
			return array($r);
		}

		$ret = array( 0 => $r );
		if(!$array){
			$ret[1] = null;
		}
		$r = fgets( $this->fp , 512 );
		$this->debugEchoLine('Server: ',$r);
		while(( '.' !=trim($r) ) && ( false !== $r ) && ( !feof($this->fp)) ){
			if( '.'===substr($r,0,1) && '.' !==$r){
				$r = substr($r,1);
			}
			if($array){
				$ret[] = $r ;
			}else{
				$ret[1] .= $r ;
			}
			$r  = fgets($this->fp , 512 );
			$this->debugEchoLine('Server: ',$r);
		}
		if(isset($ret[1])){
			$ret[1] = $array ? $ret[1] : trim( $ret[1]."\r\n\r\n" );
		}
		return $ret;
	}
}
//---------------------------------
// Normal QdPop
//---------------------------------
class QdPop extends QdPopBase{

	var $name			= 'QdPop';

	function QdPop( $param = null ){
		parent::__construct( $param );
	}

}

class QdDecodeStdin extends QdDecodeBase{

	var $name			= 'QdDecodeStdin';

	function __construct( $param = null ){
		$this->QdDecodeStdin($param);
	}
	function QdDecodeStdin( $param = null ){
		parent::__construct( $param );
	}

	function getMail(){

		$this->_before_getMail();

		$content = null;
		$fp=fopen("php://stdin",'r');

		if(!is_resource($fp)){
			return $this->error("no resouce",__LINE__,false);
		}
		while( !feof($fp) ){
			$content .= fgets( $fp ,1024);
		}
		$this->set($content);

		$this->_after_getMail();
		return $content;
	}
}
class QdDecodeDirect extends QdDecodeBase{

	var $name			= 'QdDecodeDirect';

	function __construct( $param = null ){
		$this->QdDecodeDirect($param);
	}
	function QdDecodeDirect( $param = null ){
		if( !empty( $param[0] ) ){
			$this->set($param[0]);
		}
		parent::__construct( $param );
	}

	function getMail(){

		$this->_before_getMail();

		$content = $this->all();

		$this->_after_getMail();
		return $content;
	}
}
//**********************************************************
//
//
//                Main Class (Controll Class)
//
//
//**********************************************************
class QdmailReceiver extends QdmailReceiverError{

	var $name				= 'QdmailReceiver';

	static function start(){
		$type_link = array(
			'stdin'	=> 'QdDecodeStdin',
			'pop'	=> 'QdPop',
			'direct'=> 'QdDecodeDirect',
		);

		$param = func_get_args();
		$type = $param[0];

		if( is_array( $param[0] ) ){
			$param[0] = array_change_key_case( $param[0] , CASE_LOWER );
			if( isset( $param[0]['type'] ) ){
				$type = $param[0]['type'];
			}else{
				$type = key($type_link);
			}
		}else{
			array_shift( $param );
		}
		$type = strtolower($type);
		if(!isset($type_link[$type])){
			return false;
		}
		$param['name'] = 'QdmailReceiver';
		return QdmailReceiver::getInstance( $type_link[$type] , $param );
	}

	static function & getInstance( $class_name , $param = null){
		$version = (float) PHP_VERSION ;
		if( 5 > $version ){
			$obj = & new $class_name($param);
		}else{
			$obj =  new $class_name($param);
		}
		return $obj;
	}

}


//**********************************************************
//
//
//             Function Lists
//
//
//**********************************************************

if(!function_exists('qd_decode')){
	function qd_decode( $item , $charset = null, $option = null ){
		return qd_receive_mail( 'set' , 'direct' , $charset , $option );
	}
}

if(!function_exists('qdrm')){
	function Qdrm( $cmd , $param = null , $charset = null){
		return qd_receive_mail( $cmd , $param , $charset );
	}
}
if(!function_exists('qd_receive_mail')){
	function qd_receive_mail( $cmd , $param = null , $charset = null){

		static $receive		= array();
		static $error		= array();
		static $method		= array(
			'all'		=> 'all',
			'next'		=> 'next',
			'prev'		=> 'prev',
			'count'		=> 'count',
			'pointer'	=> 'pointer',
			'done'		=> 'done',
			'timeout'	=> 'timeout',
			'debug'		=> 'debug',
			'attach'	=> 'attach',
			'eof'		=> 'eof',
			'bodyall'	=> 'body',
			'body'		=> 'bodyAutoSelect',
			'text'		=> 'text',
			'html'		=> 'html',
			'ishtml'	=> 'isHtml',
			'header'	=> 'header',
			'head'		=> 'header',
			'deleteuid'	=> 'deleteUid',
			'deluid'	=> 'deleteUid',
			'pophigh'	=> 'popHigh',
			'delete'	=> 'delete',
			'del'		=> 'delete',
			'listheader'=> 'listHeader',
		);


		$cmd = strtolower( $cmd );
		if( 'pop' == $cmd ){
			$receive[0] = QdmailReceiver::start( 'pop' , $param , $charset);
		}
		if( 'stdin' == $cmd ){
			$receive[0] = QdmailReceiver::start( 'stdin' , $param , $param );
		}
		if( 'direct' == $cmd ){
			$receive[0] = QdmailReceiver::start( 'direct' , $param , $charset);
		}
		if( !is_object( $receive[0] ) ){
			return false;
		}
		if( isset( $charset ) ){
			$receive[0] -> charser( $charset );
		}
		if( 'error' == $cmd ){
			return $error;
		}
		if( 'start' == $cmd ){
			return true;
		}

		if( !isset($method[$cmd]) || !method_exists ( $receive[0] , $method[$cmd] )){
			$error[] = 'Call no decleared Method \''.$cmd.'\'';
			return false;
		}
		return $receive[0]->{$method[$cmd]}($param);
	}
}

