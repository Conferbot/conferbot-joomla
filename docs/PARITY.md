# Web widget parity: Joomla

The widget loads its look and behaviour from the Conferbot dashboard, so a plugin only has to load it and pass the store's data. Done in this plugin:

- the loader, as the widget runs it today
- the signed-in customer as the visitor context, with the identity verification hash made server side
- sales tracking
- README setup steps

Verified on Joomla 6.

## Left

- **Update server.** `conferbot.xml` has no `<updateservers>` entry yet, so Joomla can't offer updates. Add one that points at an update XML (version, download URL to a GitHub release zip, `targetplatform`), and publish the first release. Publishing needs the maintainer's go-ahead.
