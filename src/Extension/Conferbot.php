<?php

/**
 * @package     Conferbot.Plugin
 * @subpackage  System.conferbot
 *
 * Conferbot - AI Chatbot, Live Chat & Customer Support for Joomla.
 *
 * Injects the Conferbot widget loader on every frontend page and passes the
 * logged-in user identity plus site context to the widget. The only
 * credential is the Chatbot ID; appearance and behavior are managed in the
 * Conferbot dashboard and delivered at runtime.
 *
 * All computation lives in Helper\LoaderHelper (pure and unit-tested);
 * this class only wires Joomla events and params to it.
 *
 * @author     Conferbot <support@conferbot.com>
 * @copyright  (C) 2023-2026 Conferbot
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Conferbot\Plugin\System\Conferbot\Extension;

\defined('_JEXEC') or die;

use Conferbot\Plugin\System\Conferbot\Helper\LoaderHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

final class Conferbot extends CMSPlugin implements SubscriberInterface
{
    /**
     * @return  array  Event name => handler map.
     */
    public static function getSubscribedEvents(): array
    {
        return array(
            'onBeforeCompileHead'   => 'injectWidget',
            'onExtensionBeforeSave' => 'normaliseBotIdOnSave',
        );
    }

    /**
     * Adds the Conferbot loader snippet to the document head on every
     * frontend page (site client only, HTML documents only).
     *
     * @param   Event  $event  The onBeforeCompileHead event.
     *
     * @return  void
     */
    public function injectWidget(Event $event): void
    {
        $app = $this->getApplication();

        if (!$app || !$app->isClient('site')) {
            return;
        }

        // Forgiving read (users may have pasted a snippet), strict render gate.
        $botId = LoaderHelper::extractBotId((string) $this->params->get('bot_id', ''));

        if (!LoaderHelper::isValidBotId($botId)) {
            return;
        }

        $document = $app->getDocument();

        if (!$document || strtolower((string) $document->getType()) !== 'html') {
            return;
        }

        $embedType = LoaderHelper::normaliseEmbedType((string) $this->params->get('embed_type', 'live_chat'));

        $scriptUrl = trim((string) $this->params->get('script_url', ''));

        if ($scriptUrl === '') {
            $scriptUrl = LoaderHelper::DEFAULT_SCRIPT_URL;
        }

        $site = array(
            'name'     => (string) $app->get('sitename', ''),
            'url'      => Uri::root(),
            'language' => (string) $app->getLanguage()->getTag(),
        );

        $user     = null;
        $identity = $app->getIdentity();

        if ($identity && empty($identity->guest)) {
            $user = array(
                'id'    => (int) $identity->id,
                'name'  => (string) $identity->name,
                'email' => (string) $identity->email,
            );
        }

        $secret      = trim((string) $this->params->get('identity_secret', ''));
        $optionsJson = LoaderHelper::toJson(LoaderHelper::buildOptions($site, $user, $secret));

        $document->addCustomTag(LoaderHelper::render($botId, $embedType, $scriptUrl, $optionsJson));
    }

    /**
     * Save-time convenience: when this plugin is saved in the Joomla plugin
     * manager, extract the 24-hex bot ID out of whatever was pasted into the
     * Chatbot ID field (bare ID, full embed snippet, dashboard URL).
     *
     * Argument access covers both Joomla 4 (numeric legacy arguments) and
     * Joomla 5 (named concrete event arguments).
     *
     * @param   Event  $event  The onExtensionBeforeSave event.
     *
     * @return  void
     */
    public function normaliseBotIdOnSave(Event $event): void
    {
        $context = $event->getArgument('context') ?? $event->getArgument(0);
        $table   = $event->getArgument('subject') ?? $event->getArgument(1);

        if ($context !== 'com_plugins.plugin' || !is_object($table)) {
            return;
        }

        if (($table->element ?? '') !== 'conferbot' || ($table->folder ?? '') !== 'system') {
            return;
        }

        $params = new Registry($table->params ?? '{}');
        $pasted = (string) $params->get('bot_id', '');

        if ($pasted === '') {
            return;
        }

        $extracted = LoaderHelper::extractBotId($pasted);

        if ($extracted !== '' && $extracted !== $pasted) {
            $params->set('bot_id', $extracted);
            $table->params = (string) $params;
        }
    }
}
