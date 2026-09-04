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
 * Package installer script to automatically enable all 6 custom field plugins upon installation.
 */
class pkg_fields_brasilInstallerScript
{
    /**
     * Method called after the package is installed or updated.
     *
     * @param   string  $type    Action type ('install' or 'update')
     * @param   object  $parent  Parent installer object
     * @return  void
     */
    public function postflight(string $type, $parent): void
    {
        if (in_array($type, ['install', 'update', 'discover_install'], true)) {
            $this->enableAllPlugins();
        }
    }

    /**
     * Automatically enables all 6 Brazilian custom field plugins in the database.
     *
     * @return  void
     */
    private function enableAllPlugins(): void
    {
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('enabled') . ' = 1')
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote('fields'))
                ->whereIn(
                    $db->quoteName('element'),
                    ['cpf', 'cnpj', 'cep', 'telefone', 'cpfcnpj', 'pix']
                );

            $db->setQuery($query)->execute();
        } catch (\Throwable $e) {
            // Silently handle any potential database lock
        }
    }
}

/**
 * Fallback class in case Joomla cleans package name as fields_brasil
 */
class fields_brasilInstallerScript extends pkg_fields_brasilInstallerScript
{
}
