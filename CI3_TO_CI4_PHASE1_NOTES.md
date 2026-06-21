# CI3 to CI4 conversion - Phase 1

This package contains an automated first-pass migration:

- CI3 controllers moved to `app/Controllers` with namespaces.
- CI3 models moved to `app/Models` and extended from `LegacyModel`.
- Views, helpers, libraries, language files copied.
- Assets/uploads copied under `public/`.
- CI3-style compatibility shims added for `$this->db`, `$this->input`, `$this->session`, `$this->load`, `$this->output`.
- Routes from `application/config/routes.php` appended to `app/Config/Routes.php`.

This is not a final production migration. Run it, fix errors module-by-module, then replace compatibility shims with native CI4 services/models.

Suggested first test modules: Login, Home, Page.
