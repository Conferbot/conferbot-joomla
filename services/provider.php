<?php

/**
 * @package     Conferbot.Plugin
 * @subpackage  System.conferbot
 *
 * Joomla 4/5 dependency injection service provider for the Conferbot
 * system plugin.
 *
 * @author     Conferbot <support@conferbot.com>
 * @copyright  (C) 2023-2026 Conferbot
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Conferbot\Plugin\System\Conferbot\Extension\Conferbot;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the Conferbot plugin with the DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     */
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $plugin = new Conferbot(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('system', 'conferbot')
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
