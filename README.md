# Better Deal for Data (BD4D)

A WordPress plugin by Tech Matters that provides a contact/newsletter subscription form with Airtable integration.

**Website:** https://bd4d.org/
**Version:** 1.0.3
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
| **Production** | bd4d.org | `main` | Manual ("Set and Deploy" button) |
| **Staging** | bd4d-staging.mystagingwebsite.com | `main` | Manual ("Set and Deploy" button) |
| **Sandbox** | bd4dsandbox.mystagingwebsite.com | N/A | Static clone from staging (Oct 2025) |

### How Deployment Works

**Please double-check that this information is still correct before relying on these notes.**

These deployment notes are listed here for convenience, but they are not specific to this plugin.
This may change based on modifications completely unrelated to this plugin.

Pressable has **GitHub Integration** configured with **manual deployment triggers**:

1. **Source:** `wp-content/plugins/bd4d` folder in repo
2. **Destination:** `htdocs/wp-content/plugins/bd4d` on Pressable server
3. **Trigger:** Manual - click "Set and Deploy" in Pressable dashboard
4. **Result:** Plugin updated, other themes/plugins untouched

```
GitHub (main branch)              Pressable
─────────────────────             ────────────────────
wp-content/plugins/bd4d ──────►   htdocs/wp-content/plugins/bd4d
```

### ⚠️ CRITICAL: Deployment Path Configuration

The deployment paths MUST be configured to deploy **only the bd4d plugin**, not the entire `wp-content/` folder.

**Correct Pressable settings:**
```
Repository Directory to Deploy From: wp-content/plugins/bd4d
Deployment Path:                     htdocs/wp-content/plugins/bd4d
```

**Why this matters:** The server has themes and plugins that are NOT in this git repo:

| On Server (not in git) |
|------------------------|
| Divi theme |
| Divi child theme |
| autoptimize, cloudflare, divi-pixel, wordpress-seo, etc. |

If you deploy the entire `wp-content/` folder, these will be **permanently deleted**.

### Deployment Workflow

There are two ways to deploy to production:

#### Option A: GitHub Deploy to Each Site (Plugin-Only Updates)

Best for: Plugin code changes only, when staging and production have different content/settings.

```
GitHub main ──► staging (Set and Deploy) ──► production (Set and Deploy)
```

1. Make changes locally
2. Run `npx grunt` to build assets (CSS/JS)
3. Commit changes (including built assets in `wp-content/plugins/bd4d/assets/`)
4. Push to `main` branch
5. Go to **bd4d-staging** in Pressable → Click "Set and Deploy" → Test changes
6. If issues found, fix locally, commit, push, repeat step 5
7. When satisfied → Go to **bd4d.org** in Pressable → Click "Set and Deploy"

**Pros:** Only updates plugin code, doesn't affect WordPress content/settings
**Cons:** Two manual deploy steps

#### Option B: Clone Staging to Production (Full Site Sync)

Best for: When staging and production should be identical mirrors.

```
GitHub main ──► staging (Set and Deploy) ──► production (Clone from staging)
```

1. Make changes locally
2. Run `npx grunt` to build assets (CSS/JS)
3. Commit and push to `main` branch
4. Go to **bd4d-staging** in Pressable → Click "Set and Deploy" → Test changes
5. If issues found, fix locally, commit, push, repeat step 4
6. When satisfied → Clone staging to production in Pressable

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

### Note on GitHub `staging` Branch

The `staging` branch in the GitHub repo is **not used** and is stale:

| Branch | Last Commit | Date |
|--------|-------------|------|
| `main` | 64aee4e | Jan 6, 2026 |
| `staging` | a8bcbbe "fix: fix submit button handling" | Mar 27, 2025 |

**204 commits behind main:**
- 202 are Dependabot dependency updates
- 2 are actual code changes (`fix: disable mobile logo fix`, `build: update dependencies`)

The staging workflow happens at the **Pressable level** (deploy to staging site first, then production), not via git branches. The `staging` branch could be deleted or kept for historical reference.

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
