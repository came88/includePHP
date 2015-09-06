<?php
defined('_JEXEC') or die( "Direct Access Is Not Allowed" );

jimport('joomla.event.plugin');

class plgContentIncludePHP extends JPlugin {

	function plgContentIncludePHP( &$subject ) {
            parent::__construct( $subject );
    }

    function onPrepareContent(&$article, &$params, $limitstart) {
		if($article->usertype != "Super Administrator" && $article->usertype != "Administrator") return true;
		$regex = "#{php}(.*?){/php}#s";
		$article->text = preg_replace_callback($regex,array($this,"execphp"), $article->text);
		$regex = "#{phpfile}(.*?){/phpfile}#s";
		$article->text = preg_replace_callback($regex,array($this,"incphp"), $article->text);
		$regex = "#{js}(.*?){/js}#s";
		$article->text = preg_replace_callback($regex,array($this,"execjs"), $article->text);
		$regex = "#{jsfile}(.*?){/jsfile}#s";
		$article->text = preg_replace_callback($regex,array($this,"incjs"), $article->text);
		$regex = "#{htmlfile}(.*?){/htmlfile}#s";
		$article->text = preg_replace_callback($regex,array($this,"inchtml"), $article->text);
	    return true;
    }

	function execphp($matches) {
		ob_start();
		eval($matches[1]);
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	function execjs($matches) {
		$output = "<script type='text/javascript'>{$matches[1]}</script>";
		return $output;
	}

	function incjs($matches) {
		$output = "<script type='text/javascript' src='{$matches[1]}'></script>";
		return $output;
	}

	function inchtml($matches) {
		$output = '';
		if(file_exists($matches[1]) && is_readable($matches[1])) {
			$body = file_get_contents($matches[1]);
			if(empty($body)) return '';
			preg_match("#<body(.*?)>(.*?)</body>#si",$body, $matches2);
			if(isset($matches2[2])) $output = $matches2[2];
			if(empty($output)) $output = $body;
		}
		return $output;
	}

	function incphp($matches) {
		if(!file_exists($matches[1])) return '';
		ob_start();
		include($matches[1]);
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
}

