<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Cep
 *
 * @copyright   (C) 2026 Uziel Almeida Oliveira <https://github.com/uzielweb>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Installation script to enable the CEP plugin only upon fresh installation.
 * If already installed, the current published/unpublished state is preserved.
 */
class PlgFieldsCepInstallerScript
{
    /**
     * Whether the plugin was already installed prior to the current installation/update run.
     *
     * @var bool
     */
    private bool $wasAlreadyInstalled = false;

    /**
     * Preflight method called before install or update.
     *
     * @param   string  $type    Action type ('install', 'update', 'discover_install')
     * @param   object  $parent  Installer object
     * @return  bool
     */
    public function preflight(string $type, $parent): bool
    {
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName('extension_id'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote('fields'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('cep'));

            $this->wasAlreadyInstalled = !empty($db->setQuery($query)->loadResult());
        } catch (\Throwable $e) {
            $this->wasAlreadyInstalled = false;
        }

        return true;
    }

    /**
     * Postflight method called after install or update.
     *
     * @param   string  $type    Action type ('install', 'update', 'discover_install')
     * @param   object  $parent  Installer object
     * @return  void
     */
    public function postflight(string $type, $parent): void
    {
        // Only enable if the plugin was not already installed before
        if (!$this->wasAlreadyInstalled) {
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
                ->where($db->quoteName('element') . ' = ' . $db->quote('cep'));

            $db->setQuery($query)->execute();
        } catch (\Throwable $e) {
            // Silently handle any database exceptions
        }
    }
}
