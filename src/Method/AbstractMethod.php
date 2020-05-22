<?php
/**
 * @package   OSpam-a-not
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2015-2019 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSpam-a-not.
 *
 * OSpam-a-not is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSpam-a-not is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSpam-a-not.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Alledia\PlgSystemOspamanot\Method;

use Alledia\Framework\Factory;
use Exception;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Alledia\Framework\Joomla\Extension\AbstractPlugin;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die();

abstract class AbstractMethod extends AbstractPlugin
{
    /**
     * @var array
     */
    protected $forms = null;

    /**
     * Standard response for use by subclasses that want to block the user for any reason
     *
     * @param string $testName
     *
     * @throws \Exception
     * @return void
     */
    protected function block($testName = null)
    {
        $stack  = debug_backtrace();
        $caller = array();
        $method = null;
        if (!empty($stack[1]['class'])) {
            $classParts = explode('\\', $stack[1]['class']);
            $caller[]   = array_pop($classParts);
        }

        if (!empty($stack[1]['function'])) {
            $caller[] = $stack[1]['function'];
            $method   = $stack[1]['function'];
        }

        if (!$testName) {
            $message = Text::_('PLG_SYSTEM_OSPAMANOT_BLOCK_GENERIC');
        } else {
            $message = Text::sprintf('PLG_SYSTEM_OSPAMANOT_BLOCK_FORM', $testName);
        }

        if ($this->params->get('logging', 0)) {
            Log::addLogger(array('text_file' => 'ospamanot.log.php'), Log::ALL);
            Log::add(join('::', $caller), Log::NOTICE, $testName);
        }

        if (Factory::getDocument()->getType() == 'html') {
            switch (strtolower($method)) {
                case 'onafterinitialise':
                case 'onafterroute':
                case 'onafterrender':
                    $app = Factory::getApplication();

                    $link = $app->input->server->get('HTTP_REFERER', '', 'URL') ?: Route::_('index.php');

                    $app->enqueueMessage($message, 'error');
                    $app->redirect(Route::_($link));
                    return;
            }
        }

        throw new Exception($message, 403);
    }

    /**
     * Check the current url for fields that might have been improperly
     * introduced in the URL and remove if present
     *
     * @param string[] $fields
     *
     * @return void
     * @throws Exception
     */
    protected function checkUrl(array $fields)
    {
        $uri   = \JUri::getInstance();
        $query = $uri->getQuery(true);
        foreach ($fields as $field) {
            if (isset($query[$field])) {
                $uri->delVar($field);
            }
        }

        if ($query != $uri->getQuery(true)) {
            Factory::getApplication()->redirect($uri);
        }
    }

    /**
     * Find all candidate forms for spam protection
     *
     * @param $text
     *
     * @return array
     */
    protected function findForms($text)
    {
        if ($this->forms === null) {
            $regexForm   = '#(<\s*form.*?>).*?(<\s*/\s*form\s*>)#sm';
            $regexFields = '#<\s*(input|button).*?type\s*=["\']([^\'"]*)[^>]*>#sm';

            $this->forms = array();
            if (preg_match_all($regexForm, $text, $matches)) {
                foreach ($matches[0] as $idx => $form) {
                    $submit = 0;
                    $text   = 0;
                    if (preg_match_all($regexFields, $form, $fields)) {
                        foreach ($fields[1] as $fdx => $field) {
                            $fieldType = $fields[2][$fdx];

                            if ($fieldType == 'submit' || ($field == 'button' && $fieldType == 'submit')) {
                                $submit++;
                            } elseif ($fieldType == 'text') {
                                $text++;
                            }
                        }
                    }

                    // Include form only if adding another text field won't break it
                    if ($text > 1 || $submit > 0) {
                        $this->forms[] = (object)array(
                            'source' => $form,
                            'endTag' => $matches[2][$idx]
                        );
                    }
                }
            }
        }

        return $this->forms;
    }
}
