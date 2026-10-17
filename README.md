# Conferbot Joomla Plugin

Embeds the [Conferbot](https://conferbot.com) AI chat widget on every frontend page of a Joomla 4, 5 or 6 site and passes the logged-in user identity plus site context to the bot.

## See it in action

Upload the package, paste the Chatbot ID, enable the plugin, and the bot is live on the site (recorded on Joomla 6.1.4, PHP 8.4). Click the GIF for the full-size MP4:

[![Conferbot on Joomla: upload the package, paste the bot ID, enable, save, open the site and chat](docs/media/install.gif)](https://github.com/Conferbot/conferbot-joomla/releases/download/v1.1.0/install.mp4)

## How it works

The plugin subscribes to `onBeforeCompileHead` and injects the standard Conferbot loader into the document head of every frontend (site client) HTML page. The widget script loads without blocking the page, and `ConferbotWidget` is called once it has loaded:

```html
<script>
  (function (d, s, id) {
    var js = d.createElement(s); js.async = true; js.id = id;
    js.src = "https://cdn.conferbot.com/dist/v1/widget.min.js";
    d.head.appendChild(js);
    js.onload = function () {
      window.ConferbotWidget("<CHATBOT_ID>", "live_chat", {
        user:       { id: "joomla-<user_id>", name, email, hash },   // logged-in user, if any
        customData: { platform: "joomla", siteName, siteUrl, siteLanguage }
      });
    };
  })(document, "script", "conferbot-js");
</script>
```

The only credential needed is the **Chatbot ID**. Everything visitors see is set per bot in the Conferbot dashboard and delivered at runtime, with nothing to configure in Joomla: flows and AI agents, live chat with handover to your team (queue position, leave a message when nobody is free, read receipts, file sharing, post-chat rating), the home screen, themes and dark mode, colours, launcher and mascot, greetings, languages and disabled URLs. The plugin does not duplicate those settings.

### Identity verification (optional)

With a secret from Conferbot (workspace settings > Security > Identity verification) in the plugin options, each logged-in user is sent with `hash = HMAC-SHA256(secret, user.id)`, computed on your server. Conferbot then trusts their name and email and can link their chats to their contact. The secret is stored in the plugin's parameters and never sent to the browser. Without it, the user's details are still passed but marked unverified, and if your workspace *requires* verification they are dropped.

`options.user` and `options.customData` become the visitor's identity/context in Conferbot chats, so the bot and human agents see the user's name, email, and site details.

## Requirements

- Joomla 4.2 or later, 5.x or 6.x (verified on 6.1.4).
- PHP 7.4 or later, and whatever your Joomla version needs (Joomla 5 and 6 need PHP 8.1+).
- A Conferbot account with a published bot ([app.conferbot.com](https://app.conferbot.com)), or the built-in demo bot to try it first.
- Visitors' browsers load `https://cdn.conferbot.com/dist/v1/widget.min.js` and connect to `wdt.conferbot.com`. If your site sends a Content Security Policy, allow both.

## Installation

### 1. Get the package

Download `plg_system_conferbot-<version>.zip` from the [latest release](https://github.com/Conferbot/conferbot-joomla/releases/latest).

To build it from a checkout instead (the manifest `conferbot.xml` must be at the top of the zip, not in a folder):

```bash
git archive --format=zip -o plg_system_conferbot.zip HEAD conferbot.xml services src language LICENSE
```

Releasing a new version: bump `<version>` in `conferbot.xml`, build the zip as above (named `plg_system_conferbot-<version>.zip`), attach it to the GitHub release `v<version>`, then update `updates.xml` (version, `downloadurl`, `infourl` and `sha256` from `shasum -a 256 <zip>`) and push it to `main`. `updates.xml` is not in the zip.

### 2. Install it

1. In the Joomla administrator go to **System > Install > Extensions**.
2. Open the **Upload Package File** tab and drop the zip on it (or click **Or browse for file**). Joomla says *Installation of the plugin was successful*.

From the command line instead (Joomla 4.1+): `php cli/joomla.php extension:install --path=/path/to/plg_system_conferbot.zip`.

Joomla installs plugins switched off, so the chat does not show until you finish step 4.

### 3. Find your Chatbot ID

The Chatbot ID is a 24-character code such as `64a1b2c3d4e5f6a7b8c9d0e1`.

1. Sign in at [app.conferbot.com](https://app.conferbot.com) and open your bot.
2. Click **Share**, then **Embed into your website**.
3. The ID is the first value in the code: `window.ConferbotWidget("64a1b2c3d4e5f6a7b8c9d0e1", "live_chat")`. It is also in the browser's address bar: `.../chatbot/64a1b2c3d4e5f6a7b8c9d0e1/embed`.

You can paste the whole embed code or the dashboard URL instead; the plugin keeps only the ID when you save.

### 4. Turn it on

1. Go to **System > Manage > Plugins**, search for *conferbot* and open **System - Conferbot**.
2. Paste the **Chatbot ID**.
3. Set **Status** to *Enabled*.
4. Click **Save & Close**. Joomla says *Plugin saved*.

## Configuration

Enable/disable is Joomla's own plugin status (System > Manage > Plugins); everything else is in the plugin's options.

**No account yet?** Paste `691c970890527a0468f9b2c9` to try our demo bot - it works right away, no signup needed.

| Setting | Description |
|---|---|
| Status | Joomla's switch. *Disabled* means no Conferbot code on any page. |
| Chatbot ID | Required to show the chat. Anything pasted is reduced to the 24-character ID on save; with no valid ID the plugin prints nothing. |
| How the chat appears | **Floating chat bubble** (`live_chat`, default) or **Popup window** (`popup_chat`). |
| Identity verification secret | Optional. From Conferbot workspace settings > Security > Identity verification. Signs logged-in users so Conferbot can trust their name and email. |
| Widget script URL | Leave as is for the Conferbot CDN. Only for staging or self-hosted widget builds. *(Advanced)* |
| Access, Ordering | Joomla's standard plugin fields. Leave **Access** on *Public* so guests get the chat too. |

How the chat looks and behaves (colours, launcher, greeting, flows, AI agent, live chat, languages, pages to hide it on) is set per bot in the Conferbot dashboard and delivered at runtime.

## Architecture

The extension class only wires Joomla events and params; all computation lives in a pure, individually tested helper:

```
conferbot-joomla/
├── conferbot.xml                              # Manifest: files, namespace, param fields
├── services/
│   └── provider.php                           # Joomla 4/5 DI service provider
├── src/
│   ├── Extension/
│   │   └── Conferbot.php                      # SubscriberInterface plugin: head injection + save-time ID normalisation
│   └── Helper/
│       └── LoaderHelper.php                   # Pure helper: bot ID parsing/validation, options, safe JSON, snippet render
├── language/en-GB/
│   ├── plg_system_conferbot.ini               # Param UI strings
│   └── plg_system_conferbot.sys.ini           # Name/description for extension lists
├── tests/                                     # PHPUnit suite (not shipped in the zip)
├── phpunit.xml
├── logo.png
└── LICENSE                                    # GPL-2.0-or-later
```

The helper never touches Joomla APIs, so the whole contract (extraction, validation, option building, script-injection safety, rendered snippet) is unit-tested without a Joomla installation.

## Tests

20 PHPUnit tests (62 assertions) cover bot ID extraction (bare ID, embed snippet, dashboard URL, whitespace, uppercase), strict validation, the demo bot ID, embed type normalisation, widget option building (guest/logged-in variants), the identity hash, script-injection safety of the inlined JSON (`</script>` in a user name cannot break out), and the rendered loader snippet.

```bash
# any PHP 7.4+ with phpunit 9:
phpunit

# or containerized:
docker run --rm -v "$PWD":/mod -v /path/to/phpunit-9.phar:/phpunit.phar:ro \
  -w /mod php:8.2-cli php /phpunit.phar
```

## Check that it works

1. Open the site in a private window. The chat bubble appears bottom right (unless the bot's dashboard settings delay it or hide it on that URL).
2. Click it, start the chat and send a message. It shows up in the Conferbot dashboard under the bot's conversations.
3. Log in on the frontend as a Joomla user and chat again: the conversation shows the user's name and email and the site details (marked verified when the identity secret is set).
4. To check the code is on the page: view the page source and search for `conferbot-js`. It appears once in the `<head>`, with `window.ConferbotWidget("<your ID>", ...)`.

## Troubleshooting

| Symptom | Cause and fix |
|---|---|
| No `conferbot-js` in the page source | The plugin is disabled, the Chatbot ID is empty or invalid, or **Access** is not *Public*. Then clear the cache: **System > Clear Cache**, plus any page cache plugin, CDN or proxy cache. |
| `conferbot-js` is there but no bubble | A Content Security Policy without `cdn.conferbot.com` / `wdt.conferbot.com`, an ad or script blocker, or the bot is unpublished or hides itself on that URL. Check the browser console. |
| Nothing on some pages only | Those pages are not HTML (feeds, JSON, raw), or the template does not output `<jdoc:include type="metadata" />` / `<jdoc:include type="scripts" />` in its head. |
| A different or second bot appears | The embed code is still pasted in the template or a custom module. Remove it; the plugin does not load twice when a `conferbot-js` script is already on the page. |
| Logged-in users are unverified | Paste the identity verification secret and make sure it matches the one in your Conferbot workspace. |

Still stuck? Write to support@conferbot.com.

## Uninstall

- **Pause it:** set the plugin's **Status** to *Disabled*. The settings are kept.
- **Remove it:** **System > Manage > Extensions**, search for *conferbot*, tick **System - Conferbot** and click **Uninstall**. Joomla removes the files and the plugin's settings (including the identity secret). Your bot and its conversations stay in your Conferbot account.

## Notes & limitations

- No API key or workspace ID is required - the widget resolves everything server-side from the Chatbot ID over Socket.IO (`wdt.conferbot.com`).
- The widget is injected on the site client only; administrator pages are never touched.
- Updates: from 1.1.1 the manifest registers an update server (`updates.xml` on `main`, served from raw.githubusercontent.com), so Joomla offers new versions under **System > Update > Extensions**. 1.1.0 and earlier have no update server: install the 1.1.1 zip over them once (settings are kept).

## License

GNU General Public License version 2 or later (GPL-2.0-or-later); see [LICENSE](LICENSE).
