<?php
/**
 * Renderer for offline XHTML output
 *
 * @author     Jan Wessely <info@jawe.net>
 */
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_INC.'inc/parser/xhtml.php');

class renderer_plugin_offlinehtml2 extends Doku_Renderer_xhtml {

	var $ext = '.html';

    /**
     * return some info
     */
    function getInfo(){
		return array(
			'author' => 'Jan Wessely',
			'email'  => 'info@jawe.net',
			'date'   => '2007-03-01',
			'name'   => 'offlinehtml2 (renderer plugin)',
			'desc'   => 'Saves XHTML files of pages in all or a set of namespaces',
			'url'    => 'http://jawe.net/wiki/proj/dokuwiki/plugins/offlinehtml',
		);
    }
 
    function internallink($id, $name = NULL, $search=NULL, $returnonly=false)
    {
        global $ID;
        // default name is based on $id as given
        $default = $this->_simpleTitle($id);

        // now first resolve and clean up the $id
        resolve_pageid(getNS($ID),$id,$exists);
        $name = $this->_getLinkTitle($name, $default, $isImage, $id);
        if ( !$isImage ) {
            if ( $exists ) {
                $class='wikilink1';
            } else {
                $class='wikilink2';
            }
        } else {
            $class='media';
        }

        //keep hash anchor
        list($id,$hash) = split('#',$id,2);

        //prepare for formating
        $link['class']  = $class;
        $link['url'] = str_replace(':', '-', $id) . $this->ext;
        $link['name']   = $name;
        $link['title']  = str_replace(':', '-', $id) . $this->ext;

        //keep hash
        if($hash) $link['url'].='#'.$hash;

        $this->doc .= $this->_formatLink($link);
    }

    function internalmedia ($src, $title=NULL, $align=NULL, $width=NULL,
                            $height=NULL, $cache=NULL)
    {
        global $conf;
        global $ID;
        resolve_mediaid(getNS($ID),$src, $exists);

        $link = array();
        $link['class']  = 'media';
        $link['target'] = '_blank';

        $link['title']  = $this->_xmlEntities($src);
        $link['url']    = 'media/'. str_replace(':', '/', $src);

        $link['name']   = $this->_media ($src, $title, $align, $width, $height, $cache);

        //output formatted
        $this->doc .= $this->_formatLink($link);
    }

    function _media ($src, $title=NULL, $align=NULL, $width=NULL,
                      $height=NULL, $cache=NULL) {

        $ret = '';

        list($ext,$mime) = mimetype($src);
        if(substr($mime,0,5) == 'image'){
            //add image tag
            $ret .= '<img src="media/'.str_replace(':', '/', $src).'"';

            $ret .= ' class="media'.$align.'"';

            if (!is_null($title)) {
                $ret .= ' title="'.$this->_xmlEntities($title).'"';
                $ret .= ' alt="'.$this->_xmlEntities($title).'"';
            }else{
                $ret .= ' alt=""';
            }

            if ( !is_null($width) )
                $ret .= ' width="'.$this->_xmlEntities($width).'"';

            if ( !is_null($height) )
                $ret .= ' height="'.$this->_xmlEntities($height).'"';

            $ret .= ' />';

        }elseif($mime == 'application/x-shockwave-flash'){
            $ret .= '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"'.
                    ' codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0"';
            if ( !is_null($width) ) $ret .= ' width="'.$this->_xmlEntities($width).'"';
            if ( !is_null($height) ) $ret .= ' height="'.$this->_xmlEntities($height).'"';
            $ret .= '>'.DOKU_LF;
            $ret .= '<param name="movie" value="media/'. str_replace(':', '/', $src).'" />'.DOKU_LF;
            $ret .= '<param name="quality" value="high" />'.DOKU_LF;
            $ret .= '<embed src="media/'. str_replace(':', '/', $src).'"'.
                    ' quality="high"';
            if ( !is_null($width) ) $ret .= ' width="'.$this->_xmlEntities($width).'"';
            if ( !is_null($height) ) $ret .= ' height="'.$this->_xmlEntities($height).'"';
            $ret .= ' type="application/x-shockwave-flash"'.
                    ' pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>'.DOKU_LF;
            $ret .= '</object>'.DOKU_LF;

        }elseif(!is_null($title)){
            // well at least we have a title to display
            $ret .= $this->_xmlEntities($title);
        }else{
            // just show the source
            $ret .= $this->_xmlEntities($src);
        }

        return $ret;
    }

    function smiley($smiley) {
        if ( array_key_exists($smiley, $this->smileys) ) {
            $title = $this->_xmlEntities($this->smileys[$smiley]);
            $this->doc .= '<img src="images/smileys/'.$this->smileys[$smiley].
                '" align="middle" alt="'.
                    $this->_xmlEntities($smiley).'" />';
        } else {
            $this->doc .= $this->_xmlEntities($smiley);
        }
    }
}
?>
