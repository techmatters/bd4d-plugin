# Better Deal for Data (BD4D)

A WordPress plugin by Tech Matters that provides a contact/newsletter subscription form with Airtable integration.

**Website:** https://bd4d.org/
**Version:** 1.0.5
**License:** GPL-2.0-or-later

## Features

- Contact/newsletter subscription form (shortcode: `[bd4d-contact-form]`)
- Airtable integration for storing user data
- Google reCAPTCHA v3 for bot protection
- Auto-reply confirmation emails
- WordPress admin settings page for configuration

## Repository Structure

```
bd4d-plugin/                        # Repo root (build tools & config)
├── Gruntfile.js                    # Build task configuration
├── package.json                    # NPM dependencies
├── composer.json                   # PHP dependencies
├── phpcs.xml                       # PHP CodeSniffer config
├── .github/                        # CI/CD workflows (not deployed)
│   └── workflows/
│       ├── wpcs.yml                # WordPress coding standards check
│       └── check-commits.yml       # Conventional commits validation
│
└── wp-content/                     # ← DEPLOYED TO PRESSABLE
    └── plugins/
        └── bd4d/                   # The WordPress plugin
            ├── bd4d.php            # Main plugin entry point
            ├── includes/
            │   ├── class-bd4d.php              # Core form & Airtable integration
            │   ├── class-google-recaptcha.php  # ReCAPTCHA verification
            │   └── settings/
            │       ├── class-settings.php              # Base settings class
            │       └── class-contact-form-settings.php # Admin settings
            ├── assets/
            │   ├── css/src/main.scss           # Styles (→ main.min.css)
            │   └── js/src/main.js              # Form JS (→ main.min.js)
            └── template-parts/
                ├── form-email.php              # Newsletter form HTML
                └── auto-reply.php              # Email template
```

## Technology Stack

- **PHP** with WordPress Plugin Architecture (requires WP 6.2+)
- **JavaScript/jQuery** for AJAX form handling
- **SCSS** for styling
- **Grunt** for build tasks (linting, compilation, minification)
- **Composer** for PHP dependencies
- Follows **WordPress-VIP-Go** coding standards

## Build Commands

```bash
npm install && composer install   # Install dependencies
npx grunt                         # Run all build tasks
npx grunt watch                   # Watch for changes
npx grunt css-js                  # Build CSS and JS only
npx grunt php                     # Run PHP linting/standards
```

## Configuration

Settings are managed in WordPress Admin under the BD4D settings page:
- Airtable Base ID, Table ID, API Token
- Google reCAPTCHA Site Key and Secret Key

## Airtable Integration

The plugin writes form submissions to Airtable via the REST API (`https://api.airtable.com/v0`).

### Airtable Fields

| Airtable Field | Form Input | Type | Required |
|----------------|------------|------|----------|
| `Email Address` | Email input | Email | No |
| `First Name` | First name input | Text | Yes |
| `Last Name` | Last name input | Text | Yes |
| `Affiliation` | Affiliation input | Text | No |
| `Form Comments` | Message textarea | Text | No |
| `Email-Opted In?` | Newsletter checkbox | Boolean | Always sent |
| `CotW-Opted In?` | Supporter checkbox | Boolean | Always sent |
| `Adoption?` | Adoption checkbox | Boolean | Always sent |

**Note:** At least one of `Email Address` or `Form Comments` must be provided for submission.

### Key Files

- **`class-bd4d.php`** - Contains `add()` method that writes to Airtable (line ~153)
- **`form-email.php`** - Form HTML template
- **`class-contact-form-settings.php`** - Admin settings for API credentials

### Adding New Fields

1. Add the field/column directly in Airtable (no staging Airtable exists)
2. Update `class-bd4d.php`:
   - Add parameter to `add()` method
   - Add field to `$data['fields']` array
   - Update `send_message()` to read from `$_POST` and pass to `add()`
3. If field comes from form:
   - Update `form-email.php` to add the HTML input
   - Update `assets/js/src/main.js` to read the input and include it in the AJAX `data` object
4. Run `npx grunt` to build assets
5. Commit (including built assets), push, deploy

### Notes

- No staging Airtable environment - all environments write to the same Airtable base
- Plugin creates new records (POST), does not update existing ones
- Auto-reply email is sent after successful Airtable write

### Rotating the Airtable Token

The form authenticates to Airtable with a **Personal Access Token (PAT)** stored in
WP Admin → BD4D → Contact Form Settings → **Airtable Token**. If that token is revoked,
expires, or loses access to the base, submissions fail with **"Unable to send message"**
and `wp-content/debug.log` shows a 403:

```
BD4D contact form Airtable API rejected submission: HTTP 403 body:
{"error":{"type":"INVALID_PERMISSIONS_OR_MODEL_NOT_FOUND", ...}}
```

To issue a replacement token:

1. In Airtable, go to **Builder Hub → Developers → Personal access tokens → Create new token**.
   - **Name:** something identifiable, e.g. `bd4d-airtable-YYYYMMDD`.
   - **Scopes:** add both `data.records:read` **and** `data.records:write`.
   - **Access:** add the base **BD4D-Relationships-Main XRM** (base ID `appgrixUjq2JjgPbP`).
     You can only grant access to a base your own account can open — confirm at
     `https://airtable.com/appgrixUjq2JjgPbP`.
2. Click **Create token** and copy the `pat…` value (shown only once).
3. In **WP Admin → BD4D → Contact Form Settings**, paste it into **Airtable Token** and save.
   Leave **Base ID** (`appgrixUjq2JjgPbP`) and **Table ID** (`tbl9KA9XVlZW1FDau`, the
   *Individual Contacts-MAIN* table) unchanged.
4. Submit the form to test. On success, `debug.log` shows
   `BD4D contact form Airtable API responded HTTP 200`.

**Tip:** create the token under a shared/service Airtable account rather than an individual's,
so it does not break when a person loses access to the base.

## Auto-Reply Email Logic

The plugin sends a confirmation email after successful form submission. The email content varies based on which checkboxes were selected.

### Email Cases

| Case | Newsletter | Supporter | Adoption |
|------|------------|-----------|----------|
| **A** | ✓ | ✗ | ✗ |
| **B** | ✗ | ✓ | ✗ |
| **C** | ✓ | ✓ | ✗ |
| **D** | any | any | ✓ |
| **E** | ✗ | ✗ | ✗ |

### Case Details

- **Case A (Newsletter only):** User subscribes to email updates. Gets unsubscribe instructions.
- **Case B (Supporter only):** User agrees to be listed as public supporter. Gets display permission confirmation.
- **Case C (Newsletter + Supporter):** User wants both. Gets combined confirmation with bullet points for both permissions plus unsubscribe instructions.
- **Case D (Adoption):** Takes priority. User wants to learn about adopting BD4D Standard. Gets personalized follow-up promise ("We will contact you personally within the next two business days").
- **Case E (No checkboxes):** User submits without selecting any options. Gets generic welcome message with no confirmation section.

### Key File

- **`auto-reply.php`** - Email template with conditional logic

## Deployment

### Pressable Environments

| Environment | URL | Branch | Deploy Method |
|-------------|-----|--------|---------------|
| **Production** | bd4d.org | `main` | Auto-deploy on push/merge to `main` (confirmed 2026-07-18) |
| **Staging** | bd4d-staging.mystagingwebsite.com | `staging` | Auto-deploy on push to `staging` (configured 2026-07-19) |
| **Sandbox** | bd4dsandbox.mystagingwebsite.com | N/A | Static clone from staging (Oct 2025) |

### How Deployment Works

**Confirmed working 2026-07-18:** merging a PR to `main` auto-deployed to production within
a couple of minutes with **no** manual "Set and Deploy" click. Only the `bd4d` plugin directory
was updated, and the deployed files were verified byte-identical to `main`.

These notes are not specific to this plugin and could still change based on Pressable-side
configuration unrelated to this repo, so re-verify if a deploy ever behaves unexpectedly.

Each Pressable site (production and staging) has its own **GitHub Integration** pointing at this
repo. A push/merge to a site's configured branch auto-deploys within a couple of minutes.

```
GitHub                             Pressable
──────────────────                 ─────────────────────────────────────────
main    branch  ─────►             production  (bd4d.org)
staging branch  ─────►             staging     (bd4d-staging.mystagingwebsite.com)

wp-content/plugins/bd4d ─────►     htdocs/wp-content/plugins/bd4d   (both sites)
```

### GitHub Integration Settings (per site)

Each site's integration (Pressable dashboard → site → GitHub Integration) uses per-directory
**Include** and **Delete** toggles. Verified configuration for both sites:

| Setting | Production | Staging |
|---------|------------|---------|
| Branch | `main` | `staging` |
| Include **plugins** directory | Yes | Yes |
| Include **themes** directory | No | No |
| Include **MU Plugins** directory | No | No |
| Delete plugin files not in repo | **No** | **No** |
| Delete theme files not in repo | **No** | **No** |
| Delete MU plugin files not in repo | **No** | **No** |

With "Include plugins: Yes" and the "Delete" toggles off, a deploy only **adds/updates** the
`bd4d` plugin and never removes or touches anything else. Note: selecting a branch in the dropdown
does nothing until you click **Set and Deploy** to commit it.

#### ⚠️ NEVER enable a "Delete … files not in repository" toggle

This repo contains **only** `wp-content/plugins/bd4d`. The server runs ~14 plugins (Divi Pixel,
Jetpack, Autoptimize, WordPress SEO, etc.) that are **not** in this repo. Enabling "Delete plugin
files not in repository" would delete **all of them** on the next deploy. Keep all three red
"Delete …" toggles **off, permanently**.

#### mu-plugins are NOT deployed

"Include MU Plugins" is off, so files under `wp-content/mu-plugins/` must be copied to the server
manually (SFTP/rsync). Routine plugin deploys never touch them.

**Server architecture:**
- `htdocs/` - Your site files (wp-content, wp-config.php) - GitHub deploys here
- `wordpress/` (symlink) - Pressable's shared WordPress core (managed by Pressable, read-only)

### Deployment Workflow

Test on staging first, then promote to production:

```
feature branch ──► staging branch ──► staging site   (test)
                          │
                          ▼ (open PR, merge)
                        main branch  ──► production    (live)
```

1. Make changes locally on a feature branch
2. Run `npx grunt` to build assets (CSS/JS)
3. Commit (including built assets in `wp-content/plugins/bd4d/assets/`)
4. **Staging:** push the work to the `staging` branch → staging site auto-deploys → test.
   (Staging is gated by HTTP Basic Auth — log in with any WP admin credential — and it writes to
   the **production** Airtable base, so test submissions land in prod data. Delete them after.)
5. **Production:** open a PR to `main`, merge (squash/rebase — linear history is enforced on
   `main`) → production auto-deploys within minutes
6. Verify on production

**Recommended: Always backup before deploying** (see `backup.sh` in repo root)

#### Alternative: Clone Staging to Production (Full Site Sync)

Best for: When staging and production should be identical mirrors (content, settings, everything).

1. Make and test changes on staging
2. When satisfied → Clone staging to production in Pressable dashboard

**Pros:** Single source of truth, staging exactly matches what goes to production
**Cons:** Copies EVERYTHING (database, uploads, settings) - overwrites any production-only content

#### Which to Choose?

| Scenario | Recommended |
|----------|-------------|
| Plugin changes only | Option A (GitHub deploy) |
| Staging/production are identical mirrors | Option B (Clone) |
| Production has unique content or settings | Option A (GitHub deploy) |
| Unsure | Option A (safer) |

**Note:** Form submissions go to Airtable, not WordPress, so no form data is lost either way.

### The GitHub `staging` Branch

As of 2026-07-19 the `staging` branch is the **deploy source for the staging site** (it was
previously stale and unused). It was fast-forwarded to `main`, and the staging site's GitHub
Integration was pointed at it (Selected Branch: `staging`). Push feature work to `staging` to
deploy it to the staging site for testing; it doesn't need to stay perfectly in sync with `main`
between tests.

> First-push note: the staging integration was (re)configured on 2026-07-19 after previously
> pointing at a deleted branch. The auto-deploy-on-push behavior is expected to work like
> production but should be confirmed on the first real push to `staging` (check the deployed
> file timestamps on the server). If it doesn't fire, deploy manually via **Set and Deploy** or
> rsync, and re-check the integration.

### GitHub Actions (CI only, not deployment)

- **wpcs.yml** - Validates WordPress-VIP-Go coding standards on PRs
- **check-commits.yml** - Enforces conventional commit messages

### Notes

- Build tools (Grunt, npm, composer configs) live at repo root but are NOT deployed
- Only the `wp-content/plugins/bd4d/` folder is deployed to Pressable
- Built assets must be committed (they're not built on the server)
- **Never deploy the entire `wp-content/` folder** - it will delete themes and other plugins not in this repo

---
*Last updated: Jan 2026*
