# Backlog

Findings that are not committed work. An issue means someone is doing it; a line here means it is
known and waiting for a decision. Newest first.

## 2026-10-28 — from the standards onboarding (#1)

- **phpcs is advisory and checks nothing yet.** CI installs `phpcs` but not WordPressCS, so
  `--standard=WordPress` cannot load and the step is ignored (by template design). Shipping
  WordPressCS would make it meaningful.
- **The gulp asset build is not checked in CI.** `package.json` has no scripts and the gulp toolchain
  predates current Node; CI covers PHP syntax only.
