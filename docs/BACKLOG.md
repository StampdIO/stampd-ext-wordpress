# Backlog

Findings that are not committed work. An issue means someone is doing it; a line here means it is
known and waiting for a decision. Newest first.

## 2026-10-31 — from the review of the 1.4.0 fix (#3)

- **The settings page prints the stored secret key into the HTML** as the password field's `value`.
  Only users who can manage options see the page, and they can read the option anyway. Fix: render
  the field empty, and keep the stored value when it is submitted blank.
- **A stamp whose reply is lost leaves the post unmarked.** If the `/hash` reply never arrives
  (timeout, dropped connection), the API has still stamped and charged, but the plugin saves no
  meta. Every retry then gets 202 "already stamped", and that branch saves nothing either. 1.4.0
  waits 150 s, which is above the API's own node timeouts. Fix: on 202, look the proof up
  (`GET /api/v2/hash` with the hash and the session from `/init`, which returns the txid) and save it.

## 2026-10-28 — from the standards onboarding (#1)

- **phpcs is advisory and checks nothing yet.** CI installs `phpcs` but not WordPressCS, so
  `--standard=WordPress` cannot load and the step is ignored (by template design). Shipping
  WordPressCS would make it meaningful.
- **The gulp asset build is not checked in CI.** `package.json` has no scripts and the gulp toolchain
  predates current Node; CI covers PHP syntax only.
