<?php
/**
 * Export offline HTML: Saves XHTML files of pages all or a set of namespaces for offline reading"
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Jan Wessely <info@jawe.net>
 */

// must be run within dokuwiki
if(!defined('DOKU_INC')) die();
 
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

define('OFFLINEHTML_TEMPLATES', dirname(__FILE__) . '/templates/');
define('OFFLINEHTML_RENDERER', 'offlinehtml2');
if (!defined('NL')) define('NL', "\n");
//HACK prevent output from css.php
define('SIMPLE_TEST', true);

require_once(DOKU_PLUGIN . 'admin.php');
require_once(DOKU_INC . 'inc/fulltext.php');
require_once(DOKU_INC . 'inc/io.php');
require_once(DOKU_INC . 'inc/parserutils.php');
require_once(DOKU_INC . 'inc/infoutils.php');
require_once(DOKU_INC . 'lib/exe/css.php');
#require_once(DOKU_INC . 'lib/exe/js.php');

/**
 */
class admin_plugin_offlinehtml2 extends DokuWiki_Admin_Plugin {
 
	var $exportpath = '';
	
	var $namespaces = '';
	
	var $ext = '.html';
	
	var $template = 'default';
	
	var $verbose = false;
	
    /**
     * return some info
     */
    function getInfo(){
		return array(
			'author' => 'Jan Wessely',
			'email'  => 'info@jawe.net',
			'date'   => '2007-03-01',
			'name'   => 'offlinehtml2 (admin plugin)',
			'desc'   => 'Saves XHTML files of pages in all or a set of namespaces',
			'url'    => 'http://jawe.net/wiki/proj/dokuwiki/plugins/offlinehtml',
		);
    }
 
    /**
     * return sort order for position in admin menu
     */
    function getMenuSort() {
		return 1010;
    }
    
    /**
     *  return a menu prompt for the admin menu
     *  NOT REQUIRED - its better to place $lang['menu'] string in localised string file
     *  only use this function when you need to vary the string returned
     */
//    function getMenuText() {
//      return 'a menu prompt';
//    }
 
    /**
     * handle user request
     */
    function handle() {
		global $conf;
		global $lang;

		$this->exportpath = $conf['savedir'] . '/offlinehtml/';
		$this->namespaces = '';

		if (!isset($_REQUEST['cmd'])) return;   // first time - nothing to do

		if (!empty($_REQUEST['path'])) {
			$this->exportpath =  $_REQUEST['path'];
			if (substr($this->exportpath, -1) != '/') {
				$this->exportpath .= '/';
			}
		}
		if (!empty($_REQUEST['ns'])) {
			$this->namespaces = strtolower($_REQUEST['ns']);
		}
		if (!empty($_REQUEST['ext'])) {
			$this->ext = $_REQUEST['ext'];
		}
		$this->verbose = isset($_REQUEST['verbose']);

		$starttime = $this->_microtime();
		$this->_export();
		msg(sprintf($this->getLang('finished'), ($this->_microtime() - $starttime)), 0);
    }
 
    /**
     * output appropriate html
     */
    function html() {
		ptln('<form action="'.wl($ID).'" method="post" class="centeralign">');
		// output hidden values to ensure dokuwiki will return back to this plugin
		ptln('  <input type="hidden" name="do"   value="admin" />');
		ptln('  <input type="hidden" name="page" value="'.$this->getPluginName().'" />');

		ptln('  <fieldset>');
		ptln('    <legend>' . $this->getLang('confsection') . '</legend>');
		ptln('    <label class="block">' . $this->getLang('path'));
		ptln('      <input type="text" name="path" maxlength="256" size="40" value="' . $this->exportpath . '"/>');
		ptln('    </label><br />');
		ptln('    <label class="block">' . $this->getLang('ns'));
		ptln('      <input type="text" name="ns" maxlength="256" size="40" value="' . $this->namespaces . '"/>');
		ptln('    </label><br />');
		ptln('    <label class="block">' . $this->getLang('ext'));
		ptln('      <input type="text" name="ext" maxlength="256" size="10" value="' . $this->ext . '"/>');
		ptln('    </label><br />');
		ptln('    <label class="block">' . $this->getLang('verbose'));
		ptln('      <input type="checkbox" name="verbose"' . ($this->verbose ? ' checked="checked"' : '') . '/>');
		ptln('    </label><br />');
		ptln('    <input type="submit" name="cmd[export]"  value="'.$this->getLang('btn_export').'" />');
		ptln(' </fieldset>');
		ptln('</form>');
    }
	
	function _export() {
		global $conf;
		$pages = ft_pagelookup('');
		if (count($pages) == 0) {
			msg($this->getLang('nopages', 2)) ;
		} else {
			$exported = 0;
			$this->rm($this->exportpath);
			foreach ($pages as $page) {
				if ($this->_export_page($page)) {
					$exported++;
				}
			}
			if ($this->verbose) {
				msg(sprintf($this->getLang('export_count'), $exported), 0);
			}
			
			$static = 0;

			if (!@file_exists($this->exportpath . 'index' . $this->ext)) {
				$startfile = ereg_replace(':', '/', $conf['start']) . $this->ext;
				$startfile = $this->_make_offline_page_href($startfile);
				$this->_copy($this->exportpath . $startfile, $this->exportpath . 'index' . $this->ext);
				$static++;
			}

			io_saveFile($this->exportpath . 'style.css', $this->_generate_stylesheet('screen'));
			$static++;
			io_saveFile($this->exportpath . 'print.css', $this->_generate_stylesheet('print'));
			$static++;
			io_saveFile($this->exportpath . 'script.js', $this->_generate_js());
			$static++;

			$static += $this->_dircopy(DOKU_INC . 'lib/tpl/' . $this->template . '/images', $this->exportpath . 'images');
			$static += $this->_dircopy(DOKU_INC . 'lib/images/interwiki', $this->exportpath . 'images/interwiki');
			$static += $this->_dircopy(DOKU_INC . 'lib/images/smileys', $this->exportpath . 'images/smileys');
			$static += $this->_dircopy($conf['mediadir'], $this->exportpath . 'media');

			if ($this->verbose) {
				msg(sprintf($this->getLang('static_count'), $static), 0);
			}
		}
	}
	
	function _export_page($page) {
		$success = false;
		//ensure $id is in global $ID (needed for parsing)
		global $ID;
		$keep = $ID;
		$ID   = $page;
		$file = wikiFN($page);

		if(@file_exists($file)) {
			$namespaces = !empty($this->namespaces) ? split("[ ,;]+", $this->namespaces) : null;
			if (!$namespaces || in_array(getNS($page), $namespaces)) {

				$renderer =& plugin_load('renderer', OFFLINEHTML_RENDERER);
				$renderer->ext = $this->ext;
				// clear the document
				$renderer->doc = '';

				$breadcrumbs = $this->_get_breadcrumbs($page);
				$exportfile = $this->exportpath . $this->_make_offline_page_href(ereg_replace(':', '/', $page) . $this->ext);
				$content = p_render(OFFLINEHTML_RENDERER, p_get_instructions(io_readFile($file)), $info);
				//$content = p_cached_output($file, OFFLINEHTML_RENDERER, $page);
				$vars = array(
					'breadcrumbs' => $breadcrumbs,
					'content' => $content
				);
				$html = $this->_parse_tpl(OFFLINEHTML_TEMPLATES . $this->template . '.php', $vars);

				if (io_saveFile($exportfile, $html)) {
					if ($this->verbose) {
						msg(sprintf($this->getLang('exported'), $page), 1);
					}
					$success = true;
				} else {
					msg(sprintf($this->getLang('error_exporting'), $page), -1);
				}
			}
		} else {
			msg(sprintf($this->getLang('no_such_page'), $page), -1);
		}

		//restore ID (just in case)
		$ID = $keep;
		return $success;
	}
	
	function _generate_stylesheet($style = 'screen') {
		global $conf;
		$files = $this->_get_stylesheet_files($style);
		
		ob_start();
		css_interwiki();
		css_filetypes();
		
		foreach($files as $file => $location) {
			print css_loadfile($file, '');
		}
		
		$css = ob_get_contents();
		ob_end_clean();
		
		$inifile = DOKU_INC . 'lib/tpl/' . $this->template . '/style.ini';
		if (@file_exists($inifile)) {
			$ini = parse_ini_file($inifile, true);
			$css = strtr($css, $ini['replacements']);
		}

		if ($conf['compress']) {
			$css = css_compress($css);
		}
		
		return $css;
	}
	
	function _get_stylesheet_files($style = 'screen') {
		global $lang;
		
		// load template styles
	    $tplstyles = array();
		$inifile = DOKU_INC . 'lib/tpl/' . $this->template . '/style.ini';
	    if(@file_exists($inifile)){
	        $ini = parse_ini_file($inifile,true);
	        foreach($ini['stylesheets'] as $file => $mode){
	            $tplstyles[$mode][DOKU_INC . 'lib/tpl/' . $this->template . '/' . $file] = '';
	        }
	    }

	    // Array of needed files and their web locations, the latter ones
	    // are needed to fix relative paths in the stylesheets
	    $files   = array();
	    //if (isset($tplstyles['all'])) $files = array_merge($files, $tplstyles['all']);
	    if(!empty($style)){
	        $files[DOKU_INC.'lib/styles/'.$style.'.css'] = '';
	        // load plugin, template, user styles
	        $files = array_merge($files, $this->_get_plugin_stylesheet_files($style));
	        if (isset($tplstyles[$style])) $files = array_merge($files, $tplstyles[$style]);
	        $files[DOKU_CONF.'user'.$style.'.css'] = '';
	    }else{
	        $files[DOKU_INC.'lib/styles/style.css'] = '';
	        /*if($conf['spellchecker']){
	            $files[DOKU_INC.'lib/styles/spellcheck.css'] = DOKU_BASE.'lib/styles/';
	        }*/
	        // load plugin, template, user styles
	        $files = array_merge($files, $this->_get_plugin_stylesheet_files('screen'));
	        if (isset($tplstyles['screen'])) $files = array_merge($files, $tplstyles['screen']);
	        if($lang['direction'] == 'rtl'){
	            if (isset($tplstyles['rtl'])) $files = array_merge($files, $tplstyles['rtl']);
	        }
	        $files[DOKU_CONF.'userstyle.css'] = '';
		}
		return $files;
	}
	
	function _get_plugin_stylesheet_files($mode = 'screen') {
	    $list = array();
	    $plugins = plugin_list();
	    foreach ($plugins as $p){
	        if($mode == 'all'){
	            $list[DOKU_PLUGIN."$p/all.css"]  = '';
	        }elseif($mode == 'print'){
	            $list[DOKU_PLUGIN."$p/print.css"]  = '';
	        }elseif($mode == 'feed'){
	            $list[DOKU_PLUGIN."$p/feed.css"]  = '';
	        }else{
	            $list[DOKU_PLUGIN."$p/style.css"]  = '';
	            $list[DOKU_PLUGIN."$p/screen.css"] = '';
	        }
	    }
	    return $list;
	}
	
	function _generate_js() {
	    // Array of needed files
	    $files = array(
	                DOKU_INC.'lib/scripts/helpers.js',
	                DOKU_INC.'lib/scripts/events.js',
	                DOKU_INC.'lib/scripts/cookie.js',
	                DOKU_INC.'lib/scripts/script.js',
	                DOKU_INC.'lib/scripts/tw-sack.js',
	                DOKU_INC.'lib/scripts/ajax.js',
	             );
	    $files[] = DOKU_TPLINC.'script.js';

	    // get possible plugin scripts
	    $plugins = $this->_js_pluginscripts();

	    // start output buffering and build the script
	    ob_start();

	    // add some global variables
	    print "var DOKU_BASE   = './';";

	    // load files
	    foreach($files as $file){
	        echo "\n\n/* XXXXXXXXXX begin of $file XXXXXXXXXX */\n\n";
	        @readfile($file);
	        echo "\n\n/* XXXXXXXXXX end of $file XXXXXXXXXX */\n\n";
	    }

	    // init stuff
	    $this->_js_runonstart("addEvent(document,'click',closePopups)");
	    $this->_js_runonstart('addTocToggle()');

	    // load plugin scripts (suppress warnings for missing ones)
	    foreach($plugins as $plugin){
	        if (@file_exists($plugin)) {
	          echo "\n\n/* XXXXXXXXXX begin of $plugin XXXXXXXXXX */\n\n";
	          @readfile($plugin);
	          echo "\n\n/* XXXXXXXXXX end of $plugin XXXXXXXXXX */\n\n";
	        }
	    }

	    // load user script
	    @readfile(DOKU_CONF.'userscript.js');

	    // add scroll event and tooltip rewriting
	    $this->_js_runonstart('updateAccessKeyTooltip()');
	    $this->_js_runonstart('scrollToMarker()');
	    $this->_js_runonstart('focusMarker()');

	    // end output buffering and get contents
	    $js = ob_get_contents();
	    ob_end_clean();

	    // compress whitespace and comments
	    if($conf['compress']){
	        $js = $this->_js_compress($js);
	    }

		return $js;
	}
	
	function _js_pluginscripts() {
	    $list = array();
	    $plugins = plugin_list();
	    foreach ($plugins as $p){
	        $list[] = DOKU_PLUGIN."$p/script.js";
	    }
	    return $list;
	}
	
	function _js_runonstart($func) {
		echo "addInitEvent(function(){ $func; });".NL;
	}

	function _js_escape($string){
	    return str_replace('\\\\n','\\n',addslashes($string));
	}

	function _js_compress($s){
	    $i = 0;
	    $line = 0;
	    $s .= "\n";
	    $len = strlen($s);

	    // items that don't need spaces next to them
	    $chars = '^&|!+\-*\/%=\?:;,{}()<>% \t\n\r';

	    ob_start();
	    while($i < $len){
	        $ch = $s{$i};

	        // multiline comments (keeping IE conditionals)
	        if($ch == '/' && $s{$i+1} == '*' && $s{$i+2} != '@'){
	            $endC = strpos($s,'*/',$i+2);
	            if($endC === false) trigger_error('Found invalid /*..*/ comment', E_USER_ERROR);
	            $i = $endC + 2;
	            continue;
	        }

	        // singleline
	        if($ch == '/' && $s{$i+1} == '/'){
	            $endC = strpos($s,"\n",$i+2);
	            if($endC === false) trigger_error('Invalid comment', E_USER_ERROR);
	            $i = $endC;
	            continue;
	        }

	        // tricky.  might be an RE
	        if($ch == '/'){
	            // rewind, skip white space
	            $j = 1;
	            while($s{$i-$j} == ' '){
	                $j = $j + 1;
	            }
	            if( ($s{$i-$j} == '=') || ($s{$i-$j} == '(') ){
	                // yes, this is an re
	                // now move forward and find the end of it
	                $j = 1;
	                while($s{$i+$j} != '/'){
	                    while( ($s{$i+$j} != '\\') && ($s{$i+$j} != '/')){
	                        $j = $j + 1;
	                    }
	                    if($s{$i+$j} == '\\') $j = $j + 2;
	                }
	                echo substr($s,$i,$j+1);
	                $i = $i + $j + 1;
	                continue;
	            }
	        }

	        // double quote strings
	        if($ch == '"'){
	            $j = 1;
	            while( $s{$i+$j} != '"' && ($i+$j < $len)){
	                if( $s{$i+$j} == '\\' && ($s{$i+$j+1} == '"' || $s{$i+$j+1} == '\\') ){
	                    $j += 2;
	                }else{
	                    $j += 1;
	                }
	            }
	            echo substr($s,$i,$j+1);
	            $i = $i + $j + 1;
	            continue;
	        }

	        // single quote strings
	        if($ch == "'"){
	            $j = 1;
	            while( $s{$i+$j} != "'" && ($i+$j < $len)){
	                if( $s{$i+$j} == '\\' && ($s{$i+$j+1} == "'" || $s{$i+$j+1} == '\\') ){
	                    $j += 2;
	                }else{
	                    $j += 1;
	                }
	            }
	            echo substr($s,$i,$j+1);
	            $i = $i + $j + 1;
	            continue;
	        }

	        // newlines
	        if($ch == "\n" || $ch == "\r"){
	            $i = $i+1;
	            continue;
	        }

	        // leading spaces
	        if( ( $ch == ' ' ||
	              $ch == "\n" ||
	              $ch == "\t" ) &&
	            !preg_match('/['.$chars.']/',$s{$i+1}) ){
	            $i = $i+1;
	            continue;
	        }

	        // trailing spaces
	        if( ( $ch == ' ' ||
	              $ch == "\n" ||
	              $ch == "\t" ) &&
	            !preg_match('/['.$chars.']/',$s{$i-1}) ){
	            $i = $i+1;
	            continue;
	        }

	        // other chars
	        echo $ch;
	        $i = $i + 1;
	    }


	    $out = ob_get_contents();
	    ob_end_clean();
	    return $out;
	}

	function _parse_tpl($file, $vars) {
		global $ID;
		global $REV;
		global $conf;
		global $lang;

		extract($vars);
		ob_start();
		include($file);
		$res = ob_get_contents();
		ob_end_clean();
		return $res;
	}

	function _get_breadcrumbs($page) {
		global $conf;
		$items = explode(':', $page);

		$path = '';
		$result = array($conf['start'] =>  $this->_make_offline_page_href($conf['start'] . $this->ext));

		foreach($items as $item) {
			$path .= '/' . $item;
			if(file_exists($conf['datadir'] . "$path.txt"))
				$result[$item] = $this->_make_offline_page_href($path . $this->ext);
		}

		return $result;
	}

	//TODO share that code with the renderer
	function _make_offline_page_href($path) {
		$path = str_replace('/', '-', $path);
		$path = ltrim($path, '-');
		return $path;
	}
	
	function _copy($srcfile, $dstfile) {
		//error_log("Copying $srcfile to $dstfile");
		return copy($srcfile, $dstfile);
	}

	function _dircopy($srcdir, $dstdir) {
		io_mkdir_p($dstdir);
		$num = 0;
		if($curdir = opendir($srcdir)) {
			while($file = readdir($curdir)) {
				if($file != '.' && $file != '..') {
					$srcfile = $srcdir . DIRECTORY_SEPARATOR . $file;
					$dstfile = $dstdir . DIRECTORY_SEPARATOR . $file;
					if(is_file($srcfile)) {
						if(!$this->_copy($srcfile, $dstfile)) {
							msg(sprintf($this->getLang('error_copying'), $srcfile), -1);
						} else {
							$num++;
						}
					} else if(is_dir($srcfile)) {
						 $num += $this->_dircopy($srcfile, $dstfile);
					}
				}
			}
			closedir($curdir);
		}
		return $num;
	}

	function rm($dir) {
		$this->_do_rm($dir);
		clearstatcache();
	}

	function _do_rm($dir) {
		if (is_dir($dir) && ($handle = opendir($dir))) {
			while(($file = readdir($handle))) {
				if(( $file == '.' ) || ( $file == '..' ))
					continue;

				if(is_dir( $dir . DIRECTORY_SEPARATOR . $file))
					$this->_do_rm($dir . DIRECTORY_SEPARATOR . $file);
				else
					unlink($dir . DIRECTORY_SEPARATOR . $file);
			}

			closedir($handle);
			rmdir($dir);
		}
	}

	function _microtime() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

}

