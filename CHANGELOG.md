# Changelog

## 1.1.0 - 2026-10-17

- Identity verification: with the workspace secret in the plugin options, the logged-in user is sent with `hash = HMAC-SHA256(secret, user.id)`, computed on the server. The secret is never sent to the browser.
- The Chatbot ID field is no longer required to save the plugin; nothing is shown on the site until a valid ID is set.
- Field help points to Share > Embed into your website in the Conferbot dashboard.
- Verified on Joomla 6.1.4 (PHP 8.4); supports Joomla 4, 5 and 6.

## 1.0.0 - 2026-07-15

- Initial release: system plugin that loads the Conferbot widget in the head of every frontend page, with the logged-in user and site context, a save-time Chatbot ID check, and the public demo bot.
