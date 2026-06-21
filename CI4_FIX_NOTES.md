# CI4 Phase-1 Fix Notes

This ZIP fixes the current startup errors found during testing:

- Replaced incomplete/missing `LegacyInput`, `LegacySession`, `LegacyLoader`, and `LegacyOutput` classes with separate PSR-4 autoloadable files under `app/Models/`.
- Rebuilt `LegacyDatabase` and `LegacyResult` so CI3-style calls such as `row_array()`, `num_rows()`, `get_where()`, `group_start()`, `where_in()`, and `count_all_results()` can work during the transition.
- Cleaned `LegacyModel.php` to avoid duplicate class definitions.
- Kept `Home.php` using `LegacySession` instead of native CI4 session because most methods still call `$this->session->userdata()` / `set_userdata()`.
- Replaced remaining active `defined('BASEPATH')` helper guards with CI4-safe `defined('APPPATH')` guards.

Important: this is still a phase-1 bridge. The clean long-term migration should convert each module from CI3 APIs to native CI4 APIs module by module.
