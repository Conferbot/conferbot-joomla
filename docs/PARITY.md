# Web widget parity: Joomla

The widget loads its look and behaviour from the Conferbot dashboard, so a plugin only has to load it and pass the store's data. Done in this plugin:

- the loader, as the widget runs it today
- the signed-in customer as the visitor context, with the identity verification hash made server side
- sales tracking
- README setup steps

Verified on Joomla 6.

## Left

- **Update server.** Done in 1.1.1: `conferbot.xml` registers `updates.xml` (raw GitHub, `main`), which points at the release zip with its sha256.
