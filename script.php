<?php

/**
 * @package     Joomla.Package
 * @subpackage  Fields.Brasil
 *
 * @copyright   (C) 2026 Uziel Almeida Oliveira <https://github.com/uzielweb>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Package installer script to enable custom field plugins only upon fresh installation.
 * If a plugin is already installed (whether enabled or disabled), its state is preserved.
 */
class pkg_fields_brasilInstallerScript
{
    /**
     * List of plugins already installed prior to the current installation/update run.
     *
     * @var array
     */
    private array $previouslyInstalledPlugins = [];

    /**
     * All plugin elements managed by this package.
     *
     * @var array
     */
    private const MANAGED_PLUGINS = ['cpf', 'cnpj', 'cep', 'telefone', 'cpfcnpj', 'pix'];

    /**
     * Method called before install or update starts.
     * Records existing plugins so existing status is not overwritten.
     *
     * @param   string  $type    Action type ('install', 'update', 'discover_install')
     * @param   object  $parent  Parent installer object
     * @return  bool
     */
    public function preflight(string $type, $parent): bool
    {
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName('element'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote('fields'))
                ->whereIn($db->quoteName('element'), self::MANAGED_PLUGINS);

            $this->previouslyInstalledPlugins = $db->setQuery($query)->loadColumn() ?: [];
        } catch (\Throwable $e) {
            $this->previouslyInstalledPlugins = [];
        }

        return true;
    }

    /**
     * Method called after the package is installed or updated.
     * Enables only newly installed plugins without altering previously existing ones.
     *
     * @param   string  $type    Action type ('install', 'update', 'discover_install')
     * @param   object  $parent  Parent installer object
     * @return  void
     */
    public function postflight(string $type, $parent): void
    {
        // Identify which plugins were NOT previously installed in the database
        $newlyInstalledPlugins = array_diff(self::MANAGED_PLUGINS, $this->previouslyInstalledPlugins);

        if (!empty($newlyInstalledPlugins)) {
            $this->enableNewPlugins(array_values($newlyInstalledPlugins));
        }
    }

    /**
     * Automatically enables newly installed plugins in the database.
     *
     * @param   array  $pluginElements
     * @return  void
     */
    private function enableNewPlugins(array $pluginElements): void
    {
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('enabled') . ' = 1')
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote('fields'))
                ->whereIn($db->quoteName('element'), $pluginElements);

            $db->setQuery($query)->execute();
        } catch (\Throwable $e) {
            // Silently handle any database exceptions
        }
    }
}

/**
 * Fallback class in case Joomla cleans package name as fields_brasil
 */
class fields_brasilInstallerScript extends pkg_fields_brasilInstallerScript
{
}

