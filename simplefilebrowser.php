<?php
/**
 * SimpleFileBrowser Joomla! 3.x Native Component
 * @author Ivan Komlev <support@joomlaboat.com>
 * @link https://www.joomlaboat.com
 * @copyright (C) 2018-2023 Ivan Komlev
 * @license GNU/GPL
 **/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Version;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\CMSPlugin;
use CustomTables\CTMiscHelper;

//jimport('joomla.plugin.plugin');

final class SimpleFileBrowser extends CMSPlugin
//class plgContentSimpleFileBrowser extends JPlugin
{
    public function onContentPrepare($context, $article)
    {
        if (Factory::getApplication()->getName() == 'administrator')   //since   3.2
            return;
        $this->renderBrowser($article->text);
    }

    function renderBrowser(&$text): void
    {
        $options = array();
        $text_outside_textarea = $this->strip_html_tags_textarea($text);
        $fList = CTMiscHelper::getListToReplace('simplefilebrowser', $options, $text_outside_textarea, '{}', '=');

        if (count($fList) == 0)
            return;

        $i = 0;
        foreach ($options as $option) {
            $replaceWith = $this->getSimpleFileBrowser($option);

            $text = str_replace($fList[$i], $replaceWith, $text);
            $i++;
        }
    }

    function strip_html_tags_textarea($text): string
    {
        if ($text === null)
            return '';

        $value = preg_replace(
            array(
                // Remove invisible content
                '@<textarea[^>]*?>.*?</textarea>@siu',
            ),
            array(
                ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', "$0", "$0", "$0", "$0", "$0", "$0", "$0", "$0",), $text);

        return $value ?? '';
    }

    function getSimpleFileBrowser($pluginOptions): string
    {
        $document = JFactory::getDocument();

        $version_object = new Version;
        $version = (int)$version_object->getShortVersion();

        if ($version < 4) {
            $document->addCustomTag('<script src="' . URI::root(true) . '/media/jui/js/jquery.min.js"></script>');
            $document->addCustomTag('<script src="' . URI::root(true) . '/media/jui/js/bootstrap.min.js"></script>');
        } else {

            HTMLHelper::_('jquery.framework');
            $document->addCustomTag('<link rel="stylesheet" href="' . URI::root(true) . '/media/system/css/fields/switcher.css">');
        }


        $plugin = JPluginHelper::getPlugin('content', 'simplefilebrowser');
        $params = new JRegistry($plugin->params);

        $sfl_dirlocation = $params->get('sfl_dirlocation', '.' . DIRECTORY_SEPARATOR . 'images');

        require_once('browser/helper.php');
        include('browser/mod_simplefilelisterv1.0.php');

        $sfl_dirlocation = $params->get('sfl_dirlocation', '.' . DIRECTORY_SEPARATOR . 'images');
        $sfl_dirlocation = $pluginOptions;

        return SimpleFileBrowserRenderer($params,$sfl_dirlocation);
    }
}