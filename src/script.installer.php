<?php
/**
 * @package   OSpam-a-not
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2015-2023 Joomlashack.com. All rights reserved
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

use Alledia\Installer\AbstractScript;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();

require_once __DIR__ . '/library/Installer/include.php';

// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

class PlgsystemospamanotInstallerScript extends AbstractScript
{
    /**
     * @inheritDoc
     */
    protected function customPostFlight($type, $parent)
    {
        if (stripos($type, 'install') === false) {
            $this->reorderThisPlugin();
        }
    }
}
