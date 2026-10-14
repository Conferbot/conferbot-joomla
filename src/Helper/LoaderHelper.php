<?php

/**
 * @package     Conferbot.Plugin
 * @subpackage  System.conferbot
 *
 * Pure, framework-free helper behind the Conferbot system plugin.
 *
 * Everything the plugin computes lives here as static data-in/data-out
 * methods (bot ID parsing/validation, widget option building, script-safe
 * JSON, loader snippet rendering) so the whole surface is unit-testable
 * without a Joomla installation. This file must never touch Joomla APIs.
 *
 * @author     Conferbot <support@conferbot.com>
 * @copyright  (C) 2023-2026 Conferbot
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Conferbot\Plugin\System\Conferbot\Helper;

\defined('_JEXEC') || \defined('CONFERBOT_TEST_SUITE') or die;

class LoaderHelper
{
    /** Public demo bot - works out of the box so site owners can try the widget instantly. */
    public const DEMO_BOT_ID = '691c970890527a0468f9b2c9';

    /** Conferbot CDN widget loader. */
    public const DEFAULT_SCRIPT_URL = 'https://cdn.conferbot.com/dist/v1/widget.min.js';

    /** Allowed values for the second ConferbotWidget() argument. */
    public const EMBED_TYPES = array('live_chat', 'popup_chat');

    /**
     * Pulls the first 24-hex token out of whatever was pasted
     * (bare ID, full embed snippet, dashboard URL, extra whitespace).
     *
     * @param   string  $text  Raw pasted value.
     *
     * @return  string  Extracted ID, or '' when none found.
     */
    public static function extractBotId($text)
    {
        if (preg_match('/[a-f0-9]{24}/i', (string) $text, $m)) {
            return $m[0];
        }

        return '';
    }

    /**
     * Strict render-time validation: exactly 24 hex characters.
     *
     * @param   string  $id  Candidate bot ID.
     *
     * @return  bool
     */
    public static function isValidBotId($id)
    {
        return (bool) preg_match('/^[a-f0-9]{24}$/i', (string) $id);
    }

    /**
     * Normalises the embed type against the allowed list.
     *
     * @param   string  $embedType  Requested embed type.
     *
     * @return  string  A member of EMBED_TYPES ('live_chat' fallback).
     */
    public static function normaliseEmbedType($embedType)
    {
        return in_array((string) $embedType, self::EMBED_TYPES, true) ? (string) $embedType : 'live_chat';
    }

    /**
     * Builds the third argument for window.ConferbotWidget(botId, embedType, options).
     *
     * options.user carries the logged-in user identity (only when logged in);
     * options.customData carries site context. Both become the visitor's
     * context in Conferbot conversations.
     *
     * @param   array       $site  Keys: name, url, language.
     * @param   array|null  $user    Keys: id, name, email (null = guest).
     * @param   string      $secret  The workspace's identity verification secret ('' = off).
     *
     * @return  array  Widget options.
     */
    public static function buildOptions(array $site, $user = null, $secret = '')
    {
        $options = array(
            'customData' => array(
                'platform'     => 'joomla',
                'siteName'     => isset($site['name']) ? (string) $site['name'] : '',
                'siteUrl'      => isset($site['url']) ? (string) $site['url'] : '',
                'siteLanguage' => isset($site['language']) ? (string) $site['language'] : '',
            ),
        );

        if (is_array($user) && !empty($user['id'])) {
            $options['user'] = array(
                'id'    => 'joomla-' . (int) $user['id'],
                'name'  => isset($user['name']) ? (string) $user['name'] : '',
                'email' => isset($user['email']) ? (string) $user['email'] : '',
            );

            // Identity verification: the id signed with the workspace secret,
            // so Conferbot knows this is the site's own logged-in user.
            if ((string) $secret !== '') {
                $options['user']['hash'] = self::userHash($options['user']['id'], $secret);
            }
        }

        return $options;
    }

    /**
     * HMAC-SHA256 of the signed value (the user id) with the secret, hex - as Conferbot checks it.
     *
     * @param   string  $value   The user id sent to the widget.
     * @param   string  $secret  The workspace secret.
     *
     * @return  string
     */
    public static function userHash($value, $secret)
    {
        return hash_hmac('sha256', (string) $value, (string) $secret);
    }

    /**
     * JSON safe to inline inside a <script> block: JSON_HEX_* neutralise
     * user-controlled values (user name/email, site name) in the HTML
     * context, so "</script>" in a display name cannot break out.
     *
     * @param   array  $options  Widget options.
     *
     * @return  string
     */
    public static function toJson(array $options)
    {
        return json_encode($options, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }

    /**
     * Renders the canonical Conferbot loader snippet: async script tag with
     * element id "conferbot-js" that calls window.ConferbotWidget() onload.
     *
     * @param   string  $botId        Validated 24-hex bot ID.
     * @param   string  $embedType    'live_chat' or 'popup_chat'.
     * @param   string  $scriptUrl    Widget script URL.
     * @param   string  $optionsJson  Script-safe JSON from toJson().
     *
     * @return  string  Complete <script> tag.
     */
    public static function render($botId, $embedType, $scriptUrl, $optionsJson)
    {
        $flags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES;

        return "<script type=\"text/javascript\">\n"
            . "(function (d, s, id) {\n"
            . "  var js, el = d.getElementsByTagName(s)[0];\n"
            . "  if (d.getElementById(id)) { return; }\n"
            . "  js = d.createElement(s);\n"
            . "  js.async = true;\n"
            . '  js.src = ' . json_encode((string) $scriptUrl, $flags) . ";\n"
            . "  js.id = id;\n"
            . "  js.charset = 'UTF-8';\n"
            . "  if (el && el.parentNode) { el.parentNode.insertBefore(js, el); } else { d.head.appendChild(js); }\n"
            . "  js.onload = function () {\n"
            . '    window.ConferbotWidget('
            . json_encode((string) $botId, $flags) . ', '
            . json_encode((string) $embedType, $flags) . ', '
            . $optionsJson . ");\n"
            . "  };\n"
            . "})(document, 'script', 'conferbot-js');\n"
            . "</script>\n";
    }
}
