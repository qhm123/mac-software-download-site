<?php

function transcribe($aList, $aIsTopLevel = true) 
{
    $gpcList = array();
    $isMagic = get_magic_quotes_gpc();

    foreach ($aList as $key => $value) {
        if (is_array($value)) {
            $decodedKey = ($isMagic && !$aIsTopLevel)?stripslashes($key):$key;
            $decodedValue = transcribe($value, false);
        } else {
            $decodedKey = stripslashes($key);
            $decodedValue = ($isMagic)?stripslashes($value):$value;
        }
        $gpcList[$decodedKey] = $decodedValue;
    }
    return $gpcList;
}

$_GET = transcribe( $_GET ); 
$_POST = transcribe( $_POST ); 
$_REQUEST = transcribe( $_REQUEST );


function v( $str )
{
    return isset( $_REQUEST[$str] ) ? $_REQUEST[$str] : false;
}

function z( $str )
{
    return strip_tags( $str );
}

function c( $str )
{
    if(is_array($str)){
        return isset($GLOBALS['config'][$str[0]][$str[1]])?$GLOBALS['config'][$str[0]][$str[1]]:false;
    }
    return isset( $GLOBALS['config'][$str] ) ? $GLOBALS['config'][$str] : false;
}

function g( $str )
{
    return isset( $GLOBALS[$str] ) ? $GLOBALS[$str] : false;	
}

function t( $str )
{
    return trim($str);
}

function u( $str )
{
    return urlencode( $str );
}
//常用的数据过滤方法
function filter($str){
    return htmlentities(z(t($str)),ENT_COMPAT,'utf-8');
}

// render functiones
function render( $data = NULL , $layout = NULL , $sharp = 'default' )
{
    if( $layout == null )
    {
        if( is_ajax_request() )
        {
            $layout = 'ajax';
        }
        elseif( is_mobile_request() )
        {
            $layout = 'web';
        }
        else
        {
            $layout = 'web';
        }
    }

    $GLOBALS['layout'] = $layout;
    $GLOBALS['sharp'] = $sharp;

    $layout_file = AROOT . 'view/layout/' . $layout . '/' . $sharp . '.tpl.html';
    if( file_exists( $layout_file ) )
    {
        @extract( $data );
        require( $layout_file );
    }
    else
    {
        $layout_file = CROOT . 'view/layout/' . $layout . '/' . $sharp .  '.tpl.html';
        if( file_exists( $layout_file ) )
        {
            @extract( $data );
            require( $layout_file );
        }	
    }
    exit;
}

function ajax_echo( $info )
{
    if( !headers_sent() )
    {
        header("Content-Type:text/html;charset=utf-8");
        header("Expires: Thu, 01 Jan 1970 00:00:01 GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
    }

    echo $info;
    exit;
}


function info_page( $info , $title = '系统消息' )
{
    if( is_ajax_request() )
        $layout = 'ajax';
    else
        $layout = 'web';

    $data['top_title'] = $data['title'] = $title;
    $data['info'] = $info;

    render( $data , $layout , 'info' );
}

function is_ajax_request()
{
    $headers = apache_request_headers();
    return (isset( $headers['X-Requested-With'] ) && ( $headers['X-Requested-With'] == 'XMLHttpRequest' )) || (isset( $headers['x-requested-with'] ) && ( $headers['x-requested-with'] == 'XMLHttpRequest' )) || isset($_REQUEST['ajax']);
}

if (!function_exists('apache_request_headers')) 
{ 
    function apache_request_headers()
    { 
        foreach($_SERVER as $key=>$value)
        { 
            if (substr($key,0,5)=="HTTP_")
            { 
                $key=str_replace(" ","-",ucwords(strtolower(str_replace("_"," ",substr($key,5))))); 
                $out[$key]=$value; 
            }
            else
            { 
                $out[$key]=$value; 
            }
        } 

        return $out; 
    } 
} 

function is_mobile_request()
{
    $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';

    $mobile_browser = '0';

    if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))
        $mobile_browser++;

    if((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false))
        $mobile_browser++;

    if(isset($_SERVER['HTTP_X_WAP_PROFILE']))
        $mobile_browser++;

    if(isset($_SERVER['HTTP_PROFILE']))
        $mobile_browser++;

    $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
    $mobile_agents = array(
        'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
        'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
        'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
        'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
        'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
        'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
        'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
        'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
        'wapr','webc','winw','winw','xda','xda-'
    );

    if(in_array($mobile_ua, $mobile_agents))
        $mobile_browser++;

    if(strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)
        $mobile_browser++;

    // Pre-final check to reset everything if the user is on Windows
    if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false)
        $mobile_browser=0;

    // But WP7 is also Windows, with a slightly different characteristic
    if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)
        $mobile_browser++;

    if($mobile_browser>0)
        return true;
    else
        return false;
}

function uses( $m )
{
    load( 'lib/' . basename($m)  );
}

function load( $file_path ) 
{
    $file = AROOT . $file_path;
    if( file_exists( $file ) )
    {
        //echo $file;
        require_once( $file );

    }
    else
    {
        //echo CROOT . $file_path;
        require_once( CROOT . $file_path );
    }

}

function localstr( $string , $data = null )
{
    if( !isset($GLOBALS['i18n']) )
    {
        $c = c('default_language');
        if( strlen($c) < 1 ) $c = 'zh_cn';

        $lang_file = AROOT . 'local' . DS . basename($c) . '.lang.php';
        if( file_exists( $lang_file ) )
        {
            include_once( $lang_file );
            $GLOBALS['i18n'] = 'zh_cn';
        }
    }

    //print_r( $GLOBALS['language'][$GLOBALS['i18n']] );



    if( isset( $GLOBALS['language'][$GLOBALS['i18n']][$string] ) )
        $to = $GLOBALS['language'][$GLOBALS['i18n']][$string];
    else
        $to = $string;

    if( $data == null )
        return $to;
    else
    {
        if( !is_array( $data ) ) $data = array( $data );
        return vsprintf( $to , $data );
    }	

}


