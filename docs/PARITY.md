# Web widget parity: Joomla

The widget loads its look and behaviour from the Conferbot dashboard, so a plugin only has to load it and pass the store's data. Done in this plugin:

- the loader, as the widget runs it today
- the signed-in customer as the visitor context, with the identity verification hash made server side
- sales tracking
- README setup steps

Verified on Joomla 6.

## Left

- **Update server.** `conferbot_update.xml` must point at real GitHub release zips so Joomla can offer updates. Publishing a release needs the maintainer's go-ahead. After the first release, check the XML's version, download URL and `targetplatform` against it.
