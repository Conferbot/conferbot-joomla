# Joomla Extensions Directory (JED): submission kit

Everything needed to list the Conferbot system plugin (`plg_system_conferbot` 1.1.0) on the Joomla Extensions Directory. Checked against the JED knowledgebase on 2026-10-17.

## 1. Where to submit

| What | Where |
| --- | --- |
| Account | A free **Joomla.org / JED account**, registered at https://extensions.joomla.org (top right, Log in / Register). |
| Submission form | Logged in: **My Profile > Submit extension** on https://extensions.joomla.org. How-to: https://extensions.joomla.org/support/knowledgebase/for-jed-developers/submitting-an-extension |
| Rules | https://extensions.joomla.org/support/knowledgebase/submission-requirements/jed-entries-checklists |
| Download rules | https://extensions.joomla.org/support/knowledgebase/for-jed-developers/extension-distribution |
| Fees | None. JED listings are free. |

## 2. Current requirements and where we stand

| Requirement | Status |
| --- | --- |
| GPL licence (MIT/BSD are refused) | OK. `LICENSE` is GPL v2, and the manifest says "GNU General Public License version 2 or later". |
| GPL notice and `_JEXEC` guard in PHP files (JED Checker PH1/PH2) | OK. `services/provider.php` and `src/**` carry the GPL header and the `_JEXEC` check. |
| **Update server in the manifest** (required since 2017; JED Checker US1) | **Blocker, #6.** `conferbot.xml` has no `<updateservers>`. |
| Installs with the standard Joomla installer, with no encoded or obfuscated code | OK. Verified on Joomla 6.1.4 / PHP 8.4 (see the install recording). |
| A direct download link or a landing page for this extension (no file-sharing hosts) | OK: https://github.com/Conferbot/conferbot-joomla/releases/latest (asset `plg_system_conferbot-1.1.0.zip`). |
| Name: must not start with "Joomla" or contain it (only "... for Joomla" is allowed); no type words (plugin/module), version, "Free" or URLs | OK with the listing name below. The installer name `System - Conferbot` follows the "Type - Name" convention for plugins. |
| A SaaS integration may be listed as **Free** only if the service has a permanently free tier; otherwise it is listed as **Paid** | The Conferbot Free plan ($0, 600 conversations/month, no card) qualifies. List it as Free, and **confirm the free plan is permanent**. If the free tier ever goes away, the listing must switch to Paid. |
| Joomla 4/5/6 compatibility | Supports 4.2+, 5.x and 6.x. Tick J4, J5 and J6 in the form. |

JED publishes no review time, and listings wait in a queue for the JED team. Allow 1 to 3 weeks.

## 3. Blockers

1. **#6: add an update server** (`<updateservers>` plus a hosted `updates.xml`), then release 1.1.1. Without it JED Checker fails US1 and the listing is sent back.
2. Owner confirmation that the Conferbot Free plan is permanent, so the listing can say "Free".
3. Nothing on fees, tax or legal: JED takes no payment.

## 4. Listing copy (ready to paste)

- **Extension name:** `Conferbot - AI Chatbot & Live Chat`
- **Short description / intro** (one sentence): `Add an AI chatbot and live chat to your Joomla site: answer questions 24/7, capture leads and hand visitors over to your team. One ID, no code.`
- **Long description:**

  > Conferbot puts an AI-powered chat assistant on every page of your Joomla site. It answers visitors' questions instantly, day and night, collects leads, and hands conversations over to a human on your team whenever a visitor needs one.
  >
  > Setup takes a minute. Install the plugin, paste your Chatbot ID from the Conferbot dashboard, and enable it. Everything visitors see is designed in the Conferbot dashboard and delivered at runtime, so there is nothing else to configure in Joomla: flows or an AI agent trained on your content, live chat handover, the look of the widget, greetings and languages.
  >
  > Logged-in users are recognised: their name and email reach the bot and your agents as context. With an optional identity verification secret, Conferbot can trust that it really is them. The secret is used on your server and never reaches the browser.
  >
  > The plugin is free. You need a Conferbot account to run your own bot, and the free plan is enough to start. To see the widget before signing up, paste the public demo bot ID shown in the plugin.

- **Key benefits:**
  - Answers repetitive questions 24/7 with flows or an AI agent trained on your content
  - Real live chat: handover to your team with queue position, file sharing, read receipts and post-chat rating; when nobody is free, visitors leave a message
  - Lead capture: collect emails and qualify visitors automatically
  - Knows who is logged in (name, email), with optional server-side identity verification
  - One-minute setup with one ID and no template edits; the widget script loads asynchronously
  - Fully brandable in the Conferbot dashboard: themes, dark mode, colours, logo, launcher, greetings, languages
  - Works on Joomla 4.2+, 5 and 6
- **Categories:** the closest JED categories under Communication: *Live Support* (primary) and *Chat*. Pick the exact labels the form offers.
- **Tags / keywords:** `chatbot, live chat, ai chatbot, customer support, chat widget, helpdesk, lead generation`
- **Type:** Plugin. **Includes:** site. **Licence:** GPLv2 or later. **Pricing:** Free.
- **Pricing text:** "Free plugin. Requires a Conferbot account; a free plan is available (no card needed)."
- **Support:** `support@conferbot.com` (the address in the manifest, and on conferbot.com/contact). Documentation: https://github.com/Conferbot/conferbot-joomla#readme
- **Download URL:** https://github.com/Conferbot/conferbot-joomla/releases/latest
- **Demo URL:** none for Joomla. Use the reviewer steps below.
- **Privacy policy:** https://www.conferbot.com/privacy-policy (HTTP 200). **Terms:** https://www.conferbot.com/terms-of-service (HTTP 200)

### Data and GDPR statement

This statement matches the code: `src/Helper/LoaderHelper.php::buildOptions` and `src/Extension/Conferbot.php`.

- The plugin stores only its parameters in Joomla's `#__extensions` row: the bot ID, the embed type, the script URL, and the identity verification secret if one is set. It adds no tables.
- On every **front-end** HTML page (never in the administrator), once a valid bot ID is set and the plugin is enabled, the visitor's browser loads `https://cdn.conferbot.com/dist/v1/widget.min.js` and connects to Conferbot (`wdt.conferbot.com`). The page passes the widget:
  - the Chatbot ID
  - `platform: "joomla"`, plus the site name, URL and language
  - for **logged-in users only**: the user ID (`joomla-<id>`), name and email. If a secret is set, it also passes an HMAC-SHA256 of the ID, computed on the server; the secret is never sent.
- Anything the visitor types or uploads in the chat goes to Conferbot as the conversation.
- The plugin sets no cookies. The widget's own storage is covered by the Conferbot privacy policy.
- Site owners should list Conferbot as a processor in their privacy policy.

## 5. Reviewer test instructions

> 1. System > Install > Extensions > Upload Package File: `plg_system_conferbot-1.1.0.zip` from https://github.com/Conferbot/conferbot-joomla/releases/latest
> 2. System > Manage > Plugins, search "Conferbot", open **System - Conferbot**.
> 3. Paste the public demo Chatbot ID `691c970890527a0468f9b2c9` (a live "Startup Consultation Chatbot"; no Conferbot account needed). Set Status to Enabled and Save & Close.
> 4. Open the site's front end. A chat bubble appears bottom right. Click it and reply to the bot.
> 5. Optional: log in on the front end. The bot receives your name and email as context. Disabling the plugin removes the widget.

## 6. Assets (in `docs/marketplace/assets/`)

JED shows its image fields only inside the logged-in form and publishes no pixel sizes. These files are generous enough to be scaled down:

| File | Size | Content |
| --- | --- | --- |
| `logo-512x512.png` | 512x512 | Conferbot logo (from the landing site) on white |
| `screenshot-1.png` | 1280x720 | The plugin settings with the Chatbot ID pasted and Status set to Enabled (real frame, Joomla 6.1.4) |
| `screenshot-2.png` | 1280x720 | The chat bubble on the Cassiopeia front end |
| `screenshot-3.png` | 1280x720 | A conversation with the bot on the site |

A video is optional. The install video is https://github.com/Conferbot/conferbot-joomla/releases/download/v1.1.0/install.mp4. Upload it to YouTube if the form asks for a video URL.

## 7. Owner checklist

- [ ] Fix #6 (update server) and release 1.1.1.
- [ ] Optionally run [JED Checker](https://extensions.joomla.org/extension/jed-checker/) on the 1.1.1 zip.
- [ ] Confirm the Conferbot free plan is permanent, then list as Free.
- [ ] Create or log in to the JED account and accept the JED terms.
- [ ] My Profile > Submit extension: paste the copy above, upload the logo and screenshots, set the download URL, and tick Joomla 4/5/6.
- [ ] Answer the JED reviewer by email if they ask for changes.

Owner time: about 45 minutes. Review: unpublished, typically 1 to 3 weeks. No fees, tax or bank details needed.
