<?php
defined('_JEXEC') or die( "Direct Access Is Not Allowed" );

jimport('joomla.event.plugin');
jimport('joomla.user.helper');

class plgContentIncludePHP extends JPlugin {

	function plgContentIncludePHP( &$subject ) {
		parent::__construct( $subject );
	}

	function onPrepareContent(&$article, &$params, $limitstart) {
		if (!property_exists($article, "modified_by")) {
			return;
		}
//		if($article->usertype != "Super Administrator" && $article->usertype != "Administrator") return true;
//		if (!array_key_exists('Super Users', JUserHelper::getUserGroups($article->modified_by))) return true;
		if (!in_array('8', JUserHelper::getUserGroups($article->modified_by))) return true;

		$regex = "#{php}(.*?){/php}#s";
		$article->text = preg_replace_callback($regex, array($this,"execphp"), $article->text);
		$regex = "#{phpfile}(.*?){/phpfile}#s";
		$article->text = preg_replace_callback($regex, array($this,"incphp"), $article->text);
		$regex = "#{js}(.*?){/js}#s";
		$article->text = preg_replace_callback($regex, array($this,"execjs"), $article->text);
		$regex = "#{jsfile}(.*?){/jsfile}#s";
		$article->text = preg_replace_callback($regex, array($this,"incjs"), $article->text);
		$regex = "#{htmlfile}(.*?){/htmlfile}#s";
		$article->text = preg_replace_callback($regex, array($this,"inchtml"), $article->text);
		$regex = "#{css}(.*?){/css}#s";
		$article->text = preg_replace_callback($regex, array($this,"css"), $article->text);
		$regex = "#{jshead}(.*?){/jshead}#s";
		$article->text = preg_replace_callback($regex, array($this,"jshead"), $article->text);
		$regex = "#{jsheadfile}(.*?){/jsheadfile}#s";
		$article->text = preg_replace_callback($regex, array($this,"jsheadfile"), $article->text);
		$article->text = str_replace('<p>{deleteme}</p>', '', $article->text);
		return true;
	}

	/** Layer di compatibilità 1.7 -> 1.5 */
	function onContentPrepare($context, &$article, &$params, $page = 0) {
		$this->onPrepareContent($article, $params, $page);
	}

	private function execphp($matches) {
		ob_start();
		eval($matches[1]);
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	private function execjs($matches) {
		$output = "<script type='text/javascript'>{$matches[1]}</script>";
		return $output;
	}

	private function incjs($matches) {
		$output = "<script type='text/javascript' src='{$matches[1]}'></script>";
		return $output;
	}

	private function inchtml($matches) {
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

	private function incphp($matches) {
		if(!file_exists($matches[1])) return '';
		ob_start();
		include($matches[1]);
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	private function jshead($matches) {
		$head = '<script type="text/javascript">'.$matches[1].'</script>'.PHP_EOL;
		$document = &JFactory::getDocument();
		$document->addCustomTag($head);
		return "{deleteme}";
	}

	private function jsheadfile($matches) {
		$head = '<script type="text/javascript" src="'.$matches[1].'"></script>'.PHP_EOL;
		$document = &JFactory::getDocument();
		$document->addCustomTag($head);
		return "{deleteme}";
	}

	private function css($matches) {
		$head = '<style type="text/css">'.$matches[1].'</style>'.PHP_EOL;
		$document = &JFactory::getDocument();
		$document->addCustomTag($head);
		return "{deleteme}";
	}
}

