<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Telefone
 *
 * @copyright   (C) 2026 Uziel Almeida Oliveira <https://github.com/uzielweb>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Installation script to automatically enable the Telefone plugin upon installation.
 */
class PlgFieldsTelefoneInstallerScript
{
    /**
     * Postflight method called after install or update.
     *
     * @param   string  $type    Action type ('install', 'update', 'discover_install')
     * @param   object  $parent  Installer object
     * @return  void
     */
    public function postflight(string $type, $parent): void
    {
        if (in_array($type, ['install', 'update', 'discover_install'], true)) {
            $this->enablePlugin();
        }
    }

    /**
     * Enables the plugin in the #__extensions database table.
     *
     * @return  void
     */
    private function enablePlugin(): void
    {
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('enabled') . ' = 1')
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote('fields'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('telefone'));

            $db->setQuery($query)->execute();
        } catch (\Throwable $e) {
            // Ignore if database operation fails
        }
    }
}
