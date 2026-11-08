# Security Audit Report — system.veroanalysisb.com

**Date:** 2026-11-08
**Scope:** the whole application. That covers Laravel 12 routes, middleware, controllers, models, Form Requests, helpers and services, the Blade views and Vue 3/Inertia frontend, the MySQL access patterns, configuration, the public web root and the dependencies.
**Method:** manual code review, tracing each sensitive flow from route → middleware → controller → model/SQL. Every finding was checked against the source. Each fix comes with an automated regression test, and each test was **run against the original (vulnerable) code to prove it detects the issue**. Nothing was tested against production, and no secrets are reproduced in this report.

---

## 1. Executive summary

The application had several vulnerabilities that let any logged-in user, and in some cases anonymous visitors, read or change other tenants' data, take over accounts, or run code on the server:

| # | Most severe issues found | Impact |
|---|---|---|
| 1 | Remote code execution through `eval()` of request data in income-statement reports | Full server compromise by any authenticated user |
| 2 | Unauthenticated `GET /eee` running `artisan migrate` + `run:sql` | Anyone on the internet could run migrations and replay the database trigger SQL |
| 3 | Tenant-isolation bypass: removing the `/{locale}` prefix skipped the company membership check | Writes to any company by any user |
| 4 | Generic "helper" endpoints accepting any model class, record id, column and method | Read, write or delete any row of any table, in any company; mass account takeover |
| 5 | Privilege escalation in user, role and permission management | Any user could grant themselves any permission, become super-admin, plant a password, or delete users and companies |
| 6 | SQL injection across about 25 analysis/report controllers and the dashboard endpoints | Database read across tenants, including credentials |
| 7 | Open self-registration | Turned every issue above into an anonymous-internet risk |

**All Critical and High findings that could be fixed safely have been fixed** (§4). Each one has tests that fail on the old code and pass on the new code (§7). The suite has **113 tests** (43 unit and 70 feature); see §7 for the results. Some items need decisions or changes outside the code, such as rotating credentials, purging git history, server configuration, deploy flags and encrypting the Odoo credentials. Those are listed in §8 with owners' actions.

---

## 2. Application architecture & attack surface

| Area | Facts |
|---|---|
| Stack | PHP 8.4.27, laravel/framework v12.52.0, laravel/ui 4.6.1 (session auth), spatie/laravel-permission 6.24.1, spatie/laravel-medialibrary 11.20, mcamara/laravel-localization 2.3, maatwebsite/excel 3.1.67; Vue 3.5 + Inertia 3 + Vite 7; MySQL (4 connections: `mysql`, `non_banking_service`, `property_management`, `financial_planning`). |
| Tenancy | Multi-tenant by **company**. Users link to companies through `companies_users`. The available modules (CashVero, Non-Banking, Property Management, …) depend on each company's `company_systems`. |
| URL shape | `/{locale}/{company}/...` (≈1300 routes, `routes/web.php`, `routes/nonBanking.php`, `routes/propertyManagement.php`). |
| Tenant isolation | `canViewCurrentCompany` (membership) and `EnsureRouteModelsBelongToCompany` (route-bound models must carry the route company's `company_id`). |
| Roles | `super-admin`, `company-admin`, `manager`, `user` (fixed ids 1–4). Permissions are system-scoped (`User::hasPermissionTo`). Before this audit, authorization was enforced mostly in menus; only 8 of 228 controllers checked anything. |
| Entry points | Session login, password reset, user/role administration, about 25 raw-SQL analysis reports, generic AJAX helpers, Excel imports, image uploads (public disk), Odoo JSON-RPC/XML-RPC integration, PDF/Excel exports. `routes/api.php` is empty, so there are no API tokens. |
| Hosting | cPanel/Apache. `public/.htaccess` maps `.php` to the PHP handler across the whole web root, including the `public/storage` symlink. |

---

## 3. Summary table

| # | Vulnerability | Severity | Status | Tests Added |
|---|---|---|---|---|
| C1 | RCE via `eval()` of request data (`HArr::stringArrayToArray`) | Critical | **Fixed** | `Unit/Security/EvalRemovalTest` |
| C2 | Unauthenticated `GET /eee` runs `migrate` + `run:sql` | Critical | **Fixed** (route removed) | `GuestAccessTest`, `RoutesAndConfigHardeningTest` |
| C3 | Generic helpers: arbitrary model, id, column and method (edit-table-cell, delete-model, store-new-model, updateBasedOnGlobalController, get-edit-form, Truncate, multi-delete, SalesGathering destroy/edit/update/store-model) | Critical | **Fixed** | `GenericModelHelpersTest`, `CompanyScopedModelResolverTest`, `MassAssignmentAndIdorTest` |
| C4 | Tenant bypass by omitting the locale prefix | Critical | **Fixed** | `TenantIsolationTest` |
| C5 | Privilege escalation: permission sync, unhashed password mass-assignment, super-admin role assignment, delete any user or company, unprotected company/section admin | Critical | **Fixed** | `UserManagementAuthorizationTest` |
| C6 | SQL injection in analysis/dashboard/report endpoints (incl. cross-tenant `company_id` from the request body) | Critical | **Fixed** | `SqlInjectionGuardTest`, `Unit/Security/AnalysisInputGuardTest` |
| H1 | Routes without `auth` (delete company logo over GET, income-statement dates, cache deletion, upload progress) | High | **Fixed** | `GuestAccessTest`, `RoutesAndConfigHardeningTest` |
| H2 | `odoo_db_password` and all users leaked through `update-users-based-on-company-and-role`; the stored password was echoed into forms | High | **Fixed** (hidden & masked; encryption at rest **Open**, §8) | `UserManagementAuthorizationTest`, `UploadAndExposureTest`, `RoutesAndConfigHardeningTest` |
| H3 | Unvalidated uploads to the public disk (PHP content saved as `.php`; SVG/HTML XSS) | High | **Fixed** | `UploadAndExposureTest` |
| H4 | IDOR on request ids / unbound params (income statements, overdraft rates, FX rates, invoice due dates and deductions, down payments, property expenses, review flag, expense model) + mass assignment of `company_id` | High | **Fixed** (listed sites), **Open** (sites in §8.2) | `MassAssignmentAndIdorTest` |
| H5 | Public self-registration | High | **Fixed** (disabled) | `AuthenticationHardeningTest` |
| H6 | Hardcoded production DB credentials in `config/database.php`; credential-like tokens in tracked `production.txt` and `phpunit.tmp.xml` | High | **Partially fixed** (config uses `env()`); rotation & history purge **Open** | `RoutesAndConfigHardeningTest` |
| H7 | Expired trial accounts stayed logged in after login | High | **Fixed** | `AuthenticationHardeningTest` |
| M1 | Password-reset account enumeration and no rate limit | Medium | **Fixed** | `AuthenticationHardeningTest` |
| M2 | Missing/weak password policy for admin-created users | Medium | **Fixed** (`min:8`, confirmed) | `UserManagementAuthorizationTest` |
| M3 | Roleless users crash every authorization check (`roles->first()->name`) | Medium | **Fixed** | `UserManagementAuthorizationTest` |
| M4 | Stored XSS: DB-sourced menu titles, tooltip attribute, user names in the roles form | Medium | **Fixed** | `RoutesAndConfigHardeningTest` |
| M5 | Reflected-XSS echo scripts and an open mailer in `public/` | Medium | **Fixed** (files removed) | `RoutesAndConfigHardeningTest` |
| M6 | No security headers | Medium | **Fixed** (CSP **Open**, §8) | `UploadAndExposureTest` |
| M7 | Debug tooling in production (`composer install` without `--no-dev`, Ignition share button, `.env.example` sets `APP_DEBUG=true`) | Medium | **Open** (deploy/config change) | — (`app.debug` default asserted) |
| M8 | Exception handler emails full debug traces to external addresses; some controllers return `$e->getMessage()` | Medium | **Open** | — |
| M9 | `public/test.xlsx` (4.7 MB, unknown content) publicly downloadable | Medium | **Open** (needs owner review) | — |
| L1 | `eval()` of computed equation (`FinancialStatementAbleMutator`) | Low | **Fixed** (strict arithmetic whitelist) | `EvalRemovalTest` |
| L2 | Dead `eval()` of Odoo server response (`AuthTrait::getFieldAcceptableValues`) | Low | **Fixed** (parser) | `EvalRemovalTest` |
| L3 | `shell_exec` with interpolated data (`PredictSales`) | Low | **Fixed** (numeric coercion) | — |
| L4 | Session cookie `secure` unset, `encrypt=false`, `AuthenticateSession` disabled, TrustProxies not registered | Low | **Open** (env/infra decision) | `RoutesAndConfigHardeningTest` (http_only/same_site) |
| L5 | Outdated vendored libraries (dompdf 2.0.2; jQuery 3.3.1 and older copies under `public/`; abandoned php-ai/php-ml) | Low | **Open** | — |
| L6 | `Gate::before` checks the non-existent role `Super-Admin` (dead code) | Info | Documented (fixing it would *widen* access) | — |
| I1 | Vue `v-html` usage | Info | Verified safe (static strings, paginator labels, server-side translated text) | — |
| I2 | CSRF | Info | Verified enabled with no exclusions | `AuthenticationHardeningTest`, `RoutesAndConfigHardeningTest` |

---

## 4. Findings, impact and fixes

Each entry gives: location in the original code → the vulnerability and how it could be exploited → why it was vulnerable → the fix → why the fix prevents the attack.

### C1 — Remote code execution via `eval()` (Critical, Fixed)
- **Where:** `app/Helpers/HArr.php:1513-1520` (`stringArrayToArray`), called from `app/Models/Traits/Mutators/FinancialStatementAbleMutator.php:374,407`. The string is built at `:151-154` from `sub_items[*][is_percentage_of|is_cost_of_unit_of]`. Route: `POST /{locale}/{company}/income-statement/storeReport`.
- **Exploit:** any authenticated user with access to a company posts a `sub_items` value containing PHP's complex-string interpolation (for example `{${...}}`). `json_encode()` does not escape `$`, `{` or `}`, so the resulting `eval('return ["…"];')` runs the embedded expression with the web server's privileges. The unit test reproduces this with harmless marker payloads, and they executed on the original code.
- **Why vulnerable:** request data was treated as PHP source code.
- **Fix:** `stringArrayToArray()` now `json_decode`s. All 2,554 stored values in the dev DB are valid JSON. A regex fallback handles legacy `['a','b']` / `array(1,2)` literals, and nothing is ever executed. The equation `eval` at `:862` now goes through `HArr::evaluateArithmetic()`, which only evaluates strings made of digits, `+ - * / ( ) . e E`, `INF` and `NAN`. The dead Odoo `eval` (`AuthTrait.php:203`) was replaced by `AuthTrait::parseSelectionLiteral()`. `storeReport` also no longer loads another company's income statement (IDOR).
- **Why it works:** no path from request data to `eval` remains, and the remaining arithmetic `eval` cannot parse a function call, variable or string.

### C2 — Unauthenticated Artisan route (Critical, Fixed)
- **Where:** `routes/web.php:1356-1362` (`GET /eee`), outside every middleware group except `web`.
- **Exploit:** anyone can request `/eee` to run `migrate` and `run:sql`. The latter replays every SQL file in `app/Triggers/*` through `DB::unprepared` on the main and Non-Banking connections. The `Route::domain('second.con')` test route sat next to it.
- **Fix:** both routes were removed. `deploy.sh` already runs `artisan migrate --force` and `artisan run:sql`, so nothing is lost.

### C3 — Generic helpers: arbitrary model, record, column and method (Critical, Fixed)
- **Where (original):**
  - `Helpers/EditTableCellsController.php` (`POST edit-table-cell`) set `$model->{$columnName} = $data` on `('\App\Models\\'.$modelName)::find($modelId)`.
  - `Helpers/DeleteSingleRecordController.php` (`DELETE delete-model`).
  - `Helpers/HelpersController::storeNewModal` (`POST store-new-model`) wrote arbitrary columns.
  - `Helpers/UpdateBasedOnGlobalController.php` (`GET`) called `$parent->{$request->childRelationName}()`, so `delete` or `truncate` (via `__call`) worked over GET.
  - `Helpers/getEditFormController.php`.
  - `DeletingClass::truncate/multipleRowsDeleting`.
  - `SalesGatheringController::destroy` (`modelType` from the body).
  - `SalesGatheringTestController::storeModel/editModel/updateModel`.
- **Exploit:** any user could change any column of any row of any table in any company: switch on paid modules through `CompanySystem.system_name`, rewrite other tenants' data, delete users or audit logs, or truncate tables with one GET request.
- **Why vulnerable:** the class name, record id, column name and method name all came from the client, with no ownership or allow-list check. `EnsureRouteModelsBelongToCompany` only inspects route-bound models.
- **Fix:** new `app/Support/CompanyScopedModelResolver.php`:
  - **Model names:** a per-endpoint allow-list, taken from the actual Blade callers, plus an `^[A-Z][A-Za-z0-9]*$` format check and an `App\Models` / Eloquent subclass check.
  - **Record scoping:** `findForCompany()` requires a `company_id` column and scopes the lookup to the `{company}` in the route.
  - **Columns:** `assertWritableColumn()` requires a real column that is not in the protected list (`id`, `company_id`, `created_by`, `user_id`, timestamps, password and Odoo fields).
  - **Relations:** `assertRelation()` requires an allow-listed method that reflection confirms is a public, zero-argument method returning an Eloquent `Relation` and not declared on the base `Model`. This check runs *before* the method is ever called.
  - **Parent links:** parent `*_id` values must belong to the same company.
  - **Bulk and sales-gathering endpoints:** model names are limited to the uploadable models (`getUploadParamsFromType()`), and `company_id` always comes from the route.
- **Why it works:** a request can only touch allow-listed models, real non-sensitive columns and real relations, and only on rows of a company the user belongs to.

### C4 — Tenant-isolation bypass (Critical, Fixed)
- **Where:** `app/Http/Middleware/canViewCurrentCompany.php` checked only `Request()->segment(2)`, and skipped the check when that segment was non-numeric.
- **Exploit:** with `hideDefaultLocaleInURL=false`, `LaravelLocalization::setLocale()` returns `null` for a URL without a locale, so every route is *also* registered without the prefix. `POST /{otherCompanyId}/…` then puts the company id at segment 1 and a word at segment 2, and the check passed. Non-GET methods are not redirected (the package default `httpMethodsIgnored`), so every write endpoint was exposed.
- **Fix:** membership is now checked against the matched route's `{company}` parameter whenever the route URI is company-scoped (`^(locale/)?{company}/…`). The legacy `segment(2)` rule is kept for the remaining routes. Guests pass through to `auth` instead of crashing with a 500. `getCurrentCompanyId()` now also prefers the route parameter.
- **Why it works:** the company id that is checked is the same one the controller receives, whatever the URL shape.

### C5 — Privilege escalation in user, role and permission management (Critical, Fixed)
- **Where (original):**
  - `UsersAndPermissionsController::update/store/index/create` (no authorization; `$user->syncPermissions(...)`, `Role::create`).
  - `RolesAndPermissionsController::update` (target user from the body).
  - `UserController::update` (`$user->update($request->except('avatar','companies'))` stored a submitted **password unhashed**, overwrote `created_by` and `max_users`, ran `assignRole($request->role)` including `super-admin`, and had no target check). `UserController::store` had no password policy or role check.
  - `getUsersBasedOnCompanyAndRole` and `renderPermissionForUser` (no checks).
  - `RemoveUsercontroller` and `RemoveCompanycontroller` (delete any user or company).
  - The `companySection` and `section` resources (no authorization; `Company::$guarded = []`, so Odoo credentials could be written onto arbitrary users).
- **Exploit:**
  - Any authenticated user could grant themselves every permission, or submit a known bcrypt hash as another user's "password" and log in as them.
  - A company-admin could promote anyone to super-admin.
  - Anyone could delete any company with `delete:all`.
- **Fix:**
  - **New `app/Support/UserAdministration.php`.** It encodes the rules the UI already implied:
    - super-admins manage everyone, and nobody else can touch a super-admin;
    - other actors need a shared company and the `view <role>` permission;
    - assignable roles require the matching `create …` permission, never `super-admin`;
    - an actor can only grant permissions they hold, and cannot strip the ones they don't.
  - **New `superAdmin` middleware** (`app/Http/Middleware/EnsureSuperAdmin.php`) on companySection, section, remove-company, remove-company-image and user-permissions index/create/store.
  - **`UserController` writes:** fields are validated and listed explicitly, the password is set only when filled and is always hashed, `max_users` is super-admin only, and `created_by` is always the actor.
  - **`update-users-based-on-company-and-role`:** requires company membership and returns only `id` and `name`.
  - **`RemoveUsercontroller`:** requires `canManage` and refuses self-deletion.
  - **`CompanyController::update`:** only writes Odoo credentials for users of that company.
  - **`User` role helpers:** made null-safe, so roleless users are denied instead of causing a 500.
  - **`getUsersWithRoles(null)`:** no longer lists every tenant's users to company-admins.
- **Why it works:** every write path checks *who* is acting, *who* the target is, and *what* is being granted on the server, whatever the UI shows.

### C6 — SQL injection (Critical, Fixed)
- **Where (original):**
  - `SalesBreakdownAgainstAnalysisReport::getNetSalesValueSum/topAndBottomsForDashboard` (`:430-485`) concatenated `company_id`, `type`, `column`, `selectedType`, `modal_id` and the dates into `DB::select()`. These routes sit outside `{company}`, so `company_id` came from the body unchecked, which also allowed **cross-tenant reads**.
  - 13 "Against Analysis" controllers concatenated item names inside `'…'` (39 sites). Some had ad-hoc `replaceSingleQuote()` escaping, which a backslash defeats.
  - `InvoicesAgainstAnalysisReport` built an `IN ("…")` list without escaping.
  - `convertArrayToSqlString()` and `getTypeSalesAnalysisData()` (helpers).
  - `FilterColumnBasedOnAnotherColumnController` took the table and column names from the request.
  - `ZoneAgainstAnalysisReport` and `DayAgainstAnalysisReport` passed `selectRaw($request->field)`.
  - `BalancesController` used the `{currency}` and `{modelType}` route params.
  - `CustomerInvoiceDashboardController:264,317` interpolated a stored financial-institution name (second-order injection).
  - `MoneyReceivedController::markAsConfirmed` ran `DB::table($request->table_name)`.
- **Exploit:** `UNION`/boolean injection through `type`, `column`, dates or item names exfiltrated arbitrary tables (e.g. `users.password`, Odoo credentials) and other tenants' sales data.
- **Fix (layered):**
  1. **New `app/Http/Middleware/GuardAnalysisSqlInput.php`** (web group). It scopes itself by controller action, so it also covers the dynamically generated report routes. On those endpoints:
     - identifier keys (`type`, `field`, `column`, `main_type`, `firstColumnName`, …, plus the `currency`/`modelType` route params) must match `^[A-Za-z_][A-Za-z0-9_]*$`;
     - date keys must look like dates;
     - `company_id` must be digits;
     - table keys must be one of the three analysis tables;
     - no other string may contain a backslash or NUL (JSON-encoded arrays are decoded first).
  2. **New `sqlValue()` helper:** PDO-quoting, used at every item-name concatenation site. The ad-hoc escaping was removed, which also fixes how names containing `'` are displayed.
  3. **The two dashboard endpoints** now use `?` bindings, backtick-quoted validated identifiers and a company-membership check.
  4. **Allow-lists:** `BalancesController` model types, the `FilterColumn…` tables and columns, and the review-flag tables (plus `company_id` scoping).
- **Why it works:** every value is quoted by the driver or bound, and every non-bindable fragment (column, table, date, id) is limited to characters that cannot change the query's structure.

### H1 — Routes without authentication (High, Fixed)
`routes/web.php:1327-1349` sat outside the `auth` group. `GET {lang}/remove-company-image/{company}` let **anyone delete any company's logo**. `getStartDateAndEndDateOfIncomeStatementForCompany` returned any company's statement dates. The other routes in that block were only blocked by an accidental 500. They are now wrapped in `auth`, with the same paths. Logo removal is super-admin only, and the dates endpoint is scoped to the user's companies.

### H2 — Credential and user-data exposure (High, Fixed / encryption Open)
- **Before:**
  - `User::$hidden` lacked `odoo_db_password`, so any `toArray()`/JSON of a user leaked it, and `update-users-based-on-company-and-role` returned *all* users of *all* companies.
  - The profile and company forms rendered the stored password in a plain `type="text"` input.
- **Now:**
  - `odoo_db_password` is hidden and the endpoint returns only `id` and `name`.
  - The inputs are `type="password"` and empty, and a blank submission keeps the stored value.
  - Encryption at rest is **not** implemented, per the owner's decision (§8).

### H3 — Unsafe uploads (High, Fixed)
- **Before:** `CompanyController::saveLabelingData` and `SalesGatheringController::printLabelingByCustomHeaders` called `->store(..., 'public')` with no validation. Laravel names the stored file from the *content-sniffed* extension, so a PHP payload was stored as `*.php` under `public/storage`, and the cPanel handler executes it, giving RCE. The media-library path already blocks `.php` names, but it accepted SVG/HTML (stored XSS).
- **Now:**
  - Every image upload uses `nullable|file|image|mimes:jpg,jpeg,png,gif,webp|max:5120`. This covers labeling logos, company image, user avatar and profile avatar, and excludes SVG.
  - `public/.htaccess` refuses any `*.php|phtml|phar` request under `storage/`, as a second line of defence.
  - An image/PHP polyglot can still pass the image check, but it is stored with the sniffed image extension, so it cannot run. There is a test for this.

### H4 — IDOR and mass assignment (High, Fixed for listed sites)
These sites were fixed by scoping each lookup to the route company, and allow-listing the model where its name comes from the URL:
- `IncomeStatementController` update/delete/export (`findCompanyIncomeStatement`);
- `FinancialStatementAbleMutator::storeReport`;
- `HasOverdraftRate::editRate/deleteRate` (4 overdraft types);
- `ForeignExchangeRateController` edit/update/destroy (CashVero and Property Management);
- `AdjustedDueDateHistoriesController` (5 sites) and `InvoiceDeductionsController` (invoice-model allow-list `CustomerInvoice|SupplierInvoice`);
- `DownPaymentContractsController` settlements;
- `PropertyExpensesController::store` (edit scoped to the property; `property_id`/`company_id` forced);
- `ExpenseController::store`;
- `MoneyReceivedController::markAsConfirmed`;
- the `UpdateInvoiceDeductionRequest` and `StoreDownPaymentSettlementRequest` Form Requests.

Mass assignment of `company_id` was blocked in `SalesGatheringController::store`, `SalesGatheringTestController::update/storeModel/updateModel` and the generic helpers.

Remaining unscoped lookups are listed in §8.2.

### H5 — Open registration (High, Fixed)
`Auth::routes()` exposed `/register`, and a stranger's account passed `auth`. It is now `Auth::routes(['register' => false])`, and the links are already guarded by `Route::has('register')`.

### H6 — Hardcoded credentials (High, Partially fixed)
The `mysql_production` connection in `config/database.php` had literal host, database, user and password values. It now reads `PROD_DB_*` from the environment. The values are **still in git history**, as are the tokens in the tracked `production.txt` and the DB password in `phpunit.tmp.xml`. They must be rotated (§8.1). The values are not reproduced here.

### H7 — Expired trial stays logged in (High, Fixed)
`LoginController::login` authenticated an expired free-trial user, then just redirected back to the login page. Routes without `checkIfAccountExpired` stayed usable. The user is now logged out, and the session is invalidated and its token regenerated.

### Medium / Low fixes
- **M1:** `ForgotPasswordController` gets `throttle:5,1`, and the failure response is identical to the success response, so there is no enumeration. `ResetPasswordController::reset` gets `throttle:5,1`.
- **M2:** admin-created users now need `password required|min:8|confirmed`.
- **M3:** null-safe role helpers and the `superAdmin` middleware, so roleless users get a 403 rather than a 500.
- **M4:** menu titles are escaped through `safeMenuTitle()`, which keeps only `<br>`. The tooltip `data-content` is escaped. User names in the roles form are inserted as text nodes.
- **M5:** removed `public/assets/vendors/general/jquery.repeater/test-post-parse.php`, `…/test/echo.php` (both echoed `$_POST` as unescaped JSON) and `public/front/php/process-contact.php` (an open `mail()` relay). Nothing referenced them.
- **M6:** new global `SecurityHeaders` middleware: `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`, and HSTS on HTTPS. CSP was deliberately not enforced (§8).
- **L1–L3:** see C1. `PredictSales` coerces the values it passes to `shell_exec` to numbers, and the command string is unchanged for valid data.

---

## 5. Files changed

**New:**
- `app/Support/CompanyScopedModelResolver.php`, `app/Support/UserAdministration.php`
- `app/Http/Middleware/{EnsureSuperAdmin,SecurityHeaders,GuardAnalysisSqlInput}.php`
- `scripts/setup-test-db.sh`
- `tests/Feature/Security/*`, `tests/Unit/Security/*`

**Modified (security-relevant):**
- **Core and helpers:** `routes/web.php`, `app/Http/Kernel.php`, `app/Http/Middleware/canViewCurrentCompany.php`, `app/Helpers/{HArr,helpers}.php`, `app/Models/User.php`, `app/Models/Traits/Mutators/FinancialStatementAbleMutator.php`, `app/Models/Traits/Controllers/HasOverdraftRate.php`, `app/Services/Api/Traits/AuthTrait.php`, `app/Services/AI/PredictSales.php`.
- **Controllers:** the 5 `Helpers/*` controllers, `UserController`, `UsersAndPermissionsController`, `RolesAndPermissionsController`, `RemoveUsercontroller`, `CompanyController`, `ProfileController`, `Auth/{Login,ForgotPassword,ResetPassword}Controller`, `IncomeStatementController`, `HomeController`, `BalancesController`, `CustomerInvoiceDashboardController`, `FilterColumnBasedOnAnotherColumnController`, `MoneyReceivedController`, `SalesGatheringController`, `SalesGatheringTestController`, `DeletingClass`, `ExpenseController`, `AdjustedDueDateHistoriesController`, `InvoiceDeductionsController`, `DownPaymentContractsController`, `ForeignExchangeRateController` (×2), `PropertyManagements/PropertyExpensesController`, and 15 `Analysis/SalesGathering/*` controllers.
- **Form Requests:** `UpdateInvoiceDeductionRequest`, `StoreDownPaymentSettlementRequest`.
- **Views:** `profile/form`, `super_admin_view/companies/form`, `super_admin_view/roles_and_permissions/form`, `components/nav-menu/single-submenu`, `components/tool-tip`, `layouts/new-topbar`.
- **Config and web root:** `config/database.php`, `public/.htaccess`.

**Removed:** the 3 public PHP scripts listed under M5.

No commits were made. All changes are in the working tree for review.

---

## 6. Behaviour changes to be aware of

These are intentional consequences of the fixes:
- `/register` returns 404, and admins create users.
- `section`, `companySection`, `remove-company`, `remove-company-image` and `user-permissions/{user}/index|create|store` are now super-admin only (they were only linked from super-admin screens).
- Admin-set passwords must be at least 8 characters and confirmed. On user edit, a blank password keeps the existing one.
- A company-admin can no longer change their own `max_users`.
- Blank Odoo password fields keep the stored password.
- Analysis endpoints answer **422** when an identifier or date parameter contains SQL metacharacters. All values found in the views are plain snake_case.
- `delete-model` returns 404 (not `status:true`) for a record that doesn't exist or belongs to another company.
- `getCurrentCompanyId()` now returns the route company on pages like `user/{user}/edit/{company}`; previously it returned the literal string `user`.

---

## 7. Tests

### 7.1 Test infrastructure
- **Isolation:** the security suite runs against **`property_managements_test`**, a schema-only copy of the dev DB (211 tables, 87 triggers) with the 4 roles and 254 permissions seeded. It never runs against dev or production data.
- **Setup:** build it with `MYSQL_PWD=… bash scripts/setup-test-db.sh`. The script refuses any target not ending in `_test`, or equal to the source.
- **Base class:** `tests/Feature/Security/SecurityTestCase.php` fails if the connection isn't a `*_test` database, wraps every test in `DatabaseTransactions`, and skips with instructions when the schema is missing.
- **Existing tests:** unchanged. They still target the dev DB through `SMOKE_DB`.

### 7.2 Tests created (113)
| File | Covers |
|---|---|
| `Unit/Security/EvalRemovalTest` | RCE payloads never execute; legacy and JSON formats still parse; arithmetic evaluator rejects code; Odoo parser; no `eval` left in request-driven paths |
| `Unit/Security/AnalysisInputGuardTest` | 11 SQLi payload classes rejected (identifier, comment, subquery, date breakout, company id, table, backslash trick, JSON-embedded backslash, NUL); legitimate values (`Men's`, JSON with quotes, date formats) pass; non-report endpoints unaffected; scoping |
| `Unit/Security/CompanyScopedModelResolverTest` | Model allow-list (traversal, FQCN, case); destructive methods refused even if allow-listed; protected columns |
| `Unit/Security/RoutesAndConfigHardeningTest` | `/eee`, `second.con`, `/register` gone; `auth` / `superAdmin` on hardened routes; tenant and SQL guards plus headers registered; CSRF without exclusions; hidden user attributes; session and debug defaults; no hardcoded prod credentials; removed public scripts; `.htaccess` storage rule order; menu-title escaping |
| `Feature/Security/AuthenticationHardeningTest` | Registration closed; invalid and SQL-shaped credentials rejected; login lockout after 5 attempts even with the correct password; session id regenerated on login (fixation); logout clears the session and rotates the CSRF token; expired trial logged out; reset gives identical responses for known and unknown emails; reset endpoints return 429 |
| `Feature/Security/GuestAccessTest` | `/eee` returns 404; the 6 formerly open routes redirect guests; protected routes reject guests (data unchanged); non-super-admin can't delete logos |
| `Feature/Security/TenantIsolationTest` | Member allowed; locale-less bypass blocked; locale-prefixed check; legacy segment rule; guest redirect, not 500; a real company route rejects non-members |
| `Feature/Security/GenericModelHelpersTest` | Own edits still work; foreign ids return 404 with data unchanged; `CompanySystem`, `User` and `Company` refused; protected and malformed columns return 422; `delete`/`truncate`/`forceDelete`/`update`/`save` as relation return 422 with row counts unchanged; cross-company relation targets refused; delete-model scoping; store-new-model forces company, rejects foreign parents and `company_id` columns; updateBasedOnGlobal can't call methods or read other companies; get-edit-form; Truncate model allow-list |
| `Feature/Security/UserManagementAuthorizationTest` | Cross-company and super-admin targets refused; no promotion to super-admin; protected fields ignored; passwords hashed (a planted hash doesn't work); blank keeps the password; store validation; a plain user can't create users or self-escalate; grants limited to the actor's own permissions; roles-permissions target checks; user lookup membership and no sensitive fields; render-permissions; user list doesn't leak other tenants; remove-user rules; platform admin is super-admin only; roleless users denied, not crashed |
| `Feature/Security/SqlInjectionGuardTest` | `company_id` from the body must be the user's company; identifier, comment, date and company-id injection return 422; a quote-breakout payload returns no other-company data; apostrophe names still work and B's rows are never summed; subquery column rejected; filter endpoint table and column allow-list; balance route params |
| `Feature/Security/MassAssignmentAndIdorTest` | SalesGathering `company_id` forced; destroy can't delete users or foreign rows; review endpoint table allow-list and scoping; FX rates, income-statement reports, statement dates, overdraft rates, invoice balances (deductions) and down-payment settlements of other companies untouchable |
| `Feature/Security/UploadAndExposureTest` | PHP disguised as PNG, and `.php`, rejected with nothing stored; SVG and HTML rejected; polyglots can't get an executable extension; real PNGs still accepted; the Odoo password is never rendered; security headers including HSTS only over HTTPS; error JSON contains no trace |

### 7.3 Proof that the tests detect the vulnerabilities
For each suite, the fixed files were temporarily replaced with the original `HEAD` versions and the suite was re-run, then the fixes were restored. The suites that test cross-database side effects were excluded from this step: original `/eee` and `run:sql` write to the Non-Banking dev DB. The tests that still pass on the original code are deliberate regression guards for behaviour that was already correct, for example that legitimate edits still work.

| Suite | Failures on original code |
|---|---|
| TenantIsolationTest | 1/6 (the bypass; the other 5 check behaviour that was already correct) |
| GenericModelHelpersTest | 11/12 |
| UserManagementAuthorizationTest | 16/16 |
| SqlInjectionGuardTest | 6/7 |
| EvalRemovalTest | 9/9 tests, plus data sets (payloads executed) |
| AuthenticationHardeningTest | 4/9 (registration, expired trial, reset enumeration, reset throttle) |
| GuestAccessTest (excluding `/eee`) | 2/2 |
| MassAssignmentAndIdorTest | 9/9 |
| UploadAndExposureTest | 4/7 |

### 7.4 Execution results (final run, 2026-11-08)
| Run | Before changes (baseline) | After changes |
|---|---|---|
| Security suite (`tests/Unit/Security` + `tests/Feature/Security`) | — | **113 tests, 1729 assertions, all passing** |
| `--testsuite Unit` | 171 tests, all passing | **214 tests, all passing** (171 existing + 43 new) |
| `--testsuite Feature` | 420 tests: 18 errors, 182 skipped | **490 tests: the same 18 errors, the same 182 skipped** (420 existing + 70 new) |

- **No regressions:** the list of erroring Feature tests is identical before and after (diffed by test name).
- **The 18 errors are pre-existing environment problems**, not code failures. They are `Unknown database 'veroanalysisb_dev'` and missing Odoo test fixtures, in `BankStatementRowOdooGuardTest`, `NonBanking\BalanceSheetTest`, `OdooSettingStoreSafetyTest`, `ReadOdooExpenseSafetyTest` and `TempDumpPageTest`.
- **The 182 skips are existing tests** that skip themselves when their dev-DB fixtures are absent.
- **The single "PHPUnit Deprecation"** comes from the old `<filter><whitelist>` element in `phpunit.xml`, and is present in the baseline too.
- **Dependency audits:** `composer audit` and `npm audit` were not executed, because there was no network access during the audit. See §8.2.

### 7.5 How to run
```bash
MYSQL_PWD=<local root password> bash scripts/setup-test-db.sh        # once, or after schema changes
vendor/bin/phpunit tests/Unit/Security tests/Feature/Security         # security suite
vendor/bin/phpunit --testsuite Unit && vendor/bin/phpunit --testsuite Feature   # full regression
```

---

## 8. Remaining risks and recommended actions

### 8.1 Actions for the owners (cannot be done in code)
1. **Rotate** the production DB credentials (in git history: `config/database.php` before this change), the tokens in `production.txt`, and the DB password in `phpunit.tmp.xml`. Then `git rm` those two files and purge history with `git filter-repo`/BFG. Coordinate with everyone who has a clone.
2. **Production environment:** `APP_ENV=production` and `APP_DEBUG=false`. Run `composer install --no-dev` in `deploy.sh`, because debugbar and ignition are currently installed in production. Set `IGNITION_SHARING_ENABLED=false`, `SESSION_SECURE_COOKIE=true` and an `https://` `APP_URL`. Set `.env.example` to `APP_DEBUG=false`.
3. **Review and remove** `public/test.xlsx` (4.7 MB, publicly downloadable, content unknown), `public/test.html`, `public/test.js`, and the repo-root `photorec.ses`/`photorec.se2` (disk-recovery session files).
4. **Encrypt Odoo credentials at rest:** add an `encrypted` cast plus a one-time artisan command to encrypt existing rows. This was deferred by decision. The values are now hidden and masked, but they are still plaintext in the database.
5. **Exception reporting:** `app/Exceptions/Handler.php:58-70` emails full HTML debug traces, which include request data, to three external addresses. Send a short summary instead, or use a log aggregator. Stop returning `$e->getMessage()` from `PropertyManagements/ContractsController` and `PropertiesController`.

### 8.2 Code follow-ups (not fixed in this pass; each needs per-site verification)
- **Unscoped `find()` on request ids** that were found but not yet verified as exploitable. Apply the `where('company_id', $company->id)->findOrFail()` pattern:
  - `MoneyReceivedController` :290, :323-324, :404, :463, :473, :695, :1009
  - `MoneyPaymentController` :271, :300-302, :388, :458, :578, :688, :827
  - `CashExpenseController` :241, :474, :532, :553
  - `LetterOfCreditIssuanceController` :268, :289, :659-660
  - `LetterOfGuaranteeIssuanceController` :193, :257-272
  - `LetterOfGuaranteeFacilityController` :226
  - `LetterOfCreditFacilityController` :239, :252
  - `ContractsController` :215, :240, :269, :300
  - `InternalMoneyTransferController` :261-262
  - `LcSettlementInternalMoneyTransferController` :104
  - `RevenueBusinessLineController` :116-128
  - `FinancialInstitutionController` :246, :260
  - `HomeController` :451, :479
  - `SalesGatheringTestController` (`Partner::find` / `Contract::find` from the body)
  - `CustomerInvoiceDashboardController` / `AgingController` / `CollectionEffectivenessIndexController` (`modelType` from the URL; rows already scoped)
  - `PropertyManagements/OccupiedProperties…:62` and `PropertiesToBeDelivered…:65`
  - `sharable-links/quick-pricing-calculator/{pricingPlanId}`
- **Form Requests:** nearly every `authorize()` returns `true`. Move per-resource authorization into Policies over time, starting with the money, LC and LG modules.
- **Business logic:** duplicate-submission protection (idempotency keys or unique constraints on settlements and payments) and row locking (`lockForUpdate`) around balance recalculations were not audited in depth, and no race-condition tests exist yet. Recommended next.
- **MFA:** none exists. Consider TOTP for super-admins and company-admins.
- **CSP:** the views rely on inline scripts. Start with `Content-Security-Policy-Report-Only`, then move to nonces.
- **Session:** enable `AuthenticateSession` so a password change logs out other sessions. Consider `SESSION_ENCRYPT=true`. Register `TrustProxies` with the real proxy IPs if the app sits behind a load balancer.
- **Dependencies:**
  - upgrade the vendored dompdf 2.0.2 (`app/dompdf`, `storage/dompdf`), which has published advisories fixed in later 2.0.x releases; confirm with the GitHub advisories;
  - replace `public/assets/vendors` jQuery 3.3.1 (CVE-2019-11358, CVE-2020-11022/11023) and the older copies under `public/front/js`;
  - `php-ai/php-ml` is abandoned;
  - run `composer audit` and `npm audit` in CI. They were not run here because there was no network access during the audit.
- **`public/apis/ripcord*.php`:** a library loaded via `public_path()`. Move it out of the web root.
- **Tooling:** `php artisan route:list` crashes on routes to the missing `SaveFixedAtEndController` and `SaveLoanDatesController` (pre-existing). Fix it so route audits can be automated.

### 8.3 Non-security bugs noticed during the audit (not changed)
- `app/Helpers/helpers.php` (`getTypeSalesAnalysisData`) hardcodes `$report_data[...]['31-01-2026'] = 100;`, which injects a fake value into those reports.
- `ZoneSalesAnalysisReport::ZoneSalesAnalysisResult` reads `$sales_gatherings[0]` without checking for an empty result, so the dashboard "net sales for type" modal returns a 500 for an item with no rows.

### 8.4 Manual smoke checklist (recommended before deploying)
1. As company-admin: users list, create user, edit user (role unchanged), permissions page, remove user.
2. Inline edit and delete in Quick Pricing / Quotation / Financial Statement / Revenue Business Lines.
3. The "Add new" modals: cash-expense category, revenue business line → service category → service item.
4. Income statement: add, edit and delete sub-items (percentage-of and cost-of-unit), then export to Excel/PDF.
5. Labeling: upload a logo (PNG/JPG), print with logos.
6. Analysis reports and dashboards with items containing an apostrophe, for example `Men's`.
7. Super-admin: company create/edit (Odoo fields blank keeps passwords), remove company logo.
8. Password reset email flow; login lockout message.
