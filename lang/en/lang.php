<?php
/**
 * english language file for offlinehtml plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Jan Wessely <info@jawe.net>
 */
 
// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';
 
// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu'] = 'Export Offline-HTML'; 
 
$lang['btn_export'] = 'Export';
 
$lang['description'] = 'Offline HTML Plugin: Saves HTML files of pages in all or a set of namespaces';
$lang['confsection'] = 'Offline HTML plugin';
$lang['path'] = 'Export to path (required). Caution: All existing files in there will be deleted!';
$lang['ns'] = 'One or more namespaces (optional)';
$lang['ext'] = 'File extension (optional, defaults to ".html")';
$lang['verbose'] = 'Verbose output';
$lang['nopages'] = 'No pages to export';
$lang['export_count'] = 'Exported %d wiki pages';
$lang['static_count'] = 'Copied %d static files';
$lang['exported'] = 'Exported %s';
$lang['error_exporting'] = 'Error exporting %s';
$lang['no_such_page'] = 'No such wikipage: %s';
$lang['error_copying'] = 'Error copying %s';
$lang['finished'] = 'Finished exporting after %.2f seconds';
?>