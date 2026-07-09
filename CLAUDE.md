# Claude Instructions — stampd-ext-wordpress

> This file is generated from `lightstone-standards`. Edits here are local — for ecosystem-wide changes, update [lightstone-standards](https://github.com/LightstoneEnterprise/lightstone-standards).

## Project Overview

Stampd.io WordPress plugin — SHA-256 hashes post content and stamps it on the blockchain (BTC/DASH) via the stampd.io API v2, using the site owner's own API credentials.

## Stack

- **Language/Runtime**: php-wordpress
- **Doppler project**: `stampd-ext-wordpress`
- **Default branch**: `production`
- **Deploy target**: distributed WordPress plugin (not server-deployed)

## Critical Rules (from lightstone-standards)

1. `production` is the primary branch — never commit directly.
2. Every change needs a GitHub issue. See [issue-driven-development](https://github.com/LightstoneEnterprise/lightstone-standards/blob/production/standards/issue-driven-development.md).
3. All secrets via Doppler. Never hardcode. See [secrets-doppler](https://github.com/LightstoneEnterprise/lightstone-standards/blob/production/standards/secrets-doppler.md).
4. Commit format: `<type>: <description> (#<issue>)`. No signatures.
5. Pre-commit hook must pass. Don't use `--no-verify`.
6. Address all Claude PR review feedback before merging. See [pr-review-loop](https://github.com/LightstoneEnterprise/lightstone-standards/blob/production/standards/pr-review-loop.md).

## Stack-specific guidance

## WordPress plugin guidance

### Stack

- PHP 8.0+
- WordPress 6.x
- Vanilla JS or pinned web components on the frontend (no `@latest` from CDNs)

### Plugin layout

```
stampd-ext-wordpress/
├── stampd-ext-wordpress.php       # main plugin file with WP header
├── plugin/
│   ├── includes/          # PHP classes
│   ├── assets/            # JS, CSS
│   └── templates/         # template partials
└── readme.txt             # WP.org-style readme
```

### Security rules

- Sanitize ALL user input: `wp_kses_post()`, `sanitize_text_field()`, `absint()`, etc.
- Escape output: `esc_html()`, `esc_attr()`, `esc_url()`
- CSRF: WP nonces on every REST endpoint (`wp_create_nonce`, `check_ajax_referer`)
- Capability checks: `current_user_can()` before privileged actions
- Never expose API keys/secrets to the browser — proxy through WP REST API

### Commands

```bash
# Syntax check
find plugin -name "*.php" -exec php -l {} \;

# phpcs (if configured)
phpcs --standard=WordPress plugin/
```

### PR review focus

The Claude reviewer flags:
1. Unsanitized user input (raw `$_POST`, `$_GET`, `$_REQUEST`)
2. Unescaped output to the browser (XSS risk)
3. Missing nonces or capability checks
4. Direct SQL without `$wpdb->prepare`
5. Hardcoded secrets/API keys
6. Unpinned third-party CDN scripts (e.g., `@latest`)

## Repo structure

<!-- App-specific layout — fill in after scaffold -->

## Commands

<!-- Stack-specific commands inserted by scaffold -->

## Reference

- Ecosystem standards: https://github.com/LightstoneEnterprise/lightstone-standards
- Per-language conventions: `~/.lightstone-standards/standards/languages/php-wordpress.md`
