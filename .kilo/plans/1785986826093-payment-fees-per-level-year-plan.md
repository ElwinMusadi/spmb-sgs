# Payment Fees Per Academic Year and School Level

## Goal

Replace the hardcoded payment line items in the Student payment step, Finance dashboard, and PDF invoice with database-backed fees managed by Finance/Administrator. Fee values must be resolved by `academic_year_id` + `school_level_id`, while preserving historical invoice amounts.

## Decisions

- `Registration Fee` and `Entrance Assessment Test` are scoped per academic year and school level.
- `Admin Fee (Virtual Account)` remains global because it is a payment-channel fee, not a school-level fee. It is represented by a fee row with both scope foreign keys set to `null`.
- Fee edits/deletions must not change an existing invoice. A snapshot is created when an application first enters payment (`current_step = 4`) and reused thereafter.
- Only users with `payments.verify` may create, edit, deactivate, or delete fees. The existing Finance/Administrator role assignments already provide this permission.
- Existing hardcoded defaults are migrated into database seed data so the current behavior remains available after deployment.

## Data Model and Migration

1. Add a `payment_fees` table with:
   - `id`
   - `fee_type` (`registration`, `assessment`, `admin`)
   - `name`
   - `description` nullable
   - `amount` unsigned bigint
   - `academic_year_id` nullable foreign key with `nullOnDelete`
   - `school_level_id` nullable foreign key with `nullOnDelete`
   - `is_active` boolean defaulting to true
   - `sort_order` unsigned integer defaulting to 0
   - timestamps
2. Add an `application_fees` snapshot table with:
   - `id`, `application_id` cascading foreign key
   - nullable `payment_fee_id` foreign key (historical rows must survive fee deletion)
   - `fee_type`, `name`, `description`, `amount`, `sort_order`
   - timestamps
3. Add unique/index support for fee lookup. Because nullable MySQL unique columns allow duplicate `NULL` combinations, enforce duplicate scope rules in the service/Livewire validation and add indexes on `fee_type`, `academic_year_id`, `school_level_id`, and `is_active`.
4. Add `PaymentFee` and `ApplicationFee` models, factories, relationships, casts, and fillable attributes.
5. Add `Application::fees()` and `Application::hasFeeSnapshot()` relationships/helpers.
6. Add a `PaymentFeeSeeder` that creates the legacy defaults for every active Academic Year × School Level combination:
   - Registration Fee: `500000`
   - Entrance Assessment Test: `250000`
   - One global Admin Fee: `4500`
   Use `updateOrCreate` with the complete scope and fee type so seeding is idempotent.
7. Register the seeder in `DatabaseSeeder` without overwriting Finance edits on subsequent seeds. Seed only missing rows or use a clearly versioned default key; do not blindly reset existing amounts.

## Fee Resolution and Snapshot Service

1. Create a small `PaymentFeeService` used by the Finance page, Student page, invoice controller, and tests.
2. Resolve fees in this order for an application:
   - exact `academic_year_id` + `school_level_id` active fee for `registration`/`assessment`;
   - if no exact row exists, a global row for that fee type;
   - global active `admin` fee.
3. Reject ambiguous active duplicates for the same fee type and scope in CRUD validation. Return a validation error before saving rather than silently selecting one.
4. When an application first reaches step 4, create its `application_fees` snapshot inside a transaction. If a snapshot already exists, never replace it automatically.
5. Use snapshot rows for all displayed/invoiced totals once present. For old applications without a snapshot, resolve current fees and create the snapshot before rendering payment or invoice so they are stabilized.
6. Expose subtotal, admin fee, total, and formatted line-item data from the service so Blade files do not duplicate fee calculations.

## Finance UI CRUD

1. Extend `resources/views/pages/admin/finance.blade.php` with a `Payment Fees` section separate from `Payment Methods`.
2. Show a filterable/paginated table or cards containing fee name/type, amount, Academic Year, School Level, active state, and actions.
3. Add Finance-only modal CRUD fields:
   - fee type/name/description
   - amount validated as a non-negative integer
   - Academic Year selector; nullable only for global Admin Fee
   - School Level selector; required for scoped registration/assessment fees
   - active switch and sort order
4. Validate that `registration` and `assessment` require both scope values, while `admin` requires both scope values to be null. Prevent duplicate active/inactive scope/type records according to the chosen update semantics.
5. Add explicit deactivate/reactivate behavior. Prefer deactivation over deletion when a fee is referenced by an application snapshot; allow deletion only when no snapshot references it and show a confirmation modal.
6. Keep all mutating methods guarded server-side with `payments.verify`, independent of hidden buttons.

## Student Payment Step

1. Replace the two hardcoded rows and hardcoded admin fee/total in `my-application.blade.php` with the resolved/snapshotted fee collection.
2. Display each configured fee with its name, description, amount, academic year, and grade context where appropriate.
3. Calculate subtotal, Admin Fee, and Total Due from the same service output used by Finance and PDF generation.
4. Ensure entering step 4 creates the snapshot once. Payment simulation and Finance verification must use the snapshot total and must not recalculate against newly edited fees.
5. Show a clear empty/configuration error if required fees are missing instead of rendering a zero or silently falling back to hardcoded values.

## PDF Invoice and Finance Metrics

1. Remove `LINE_ITEMS`, `ADMIN_FEE`, and fixed amounts from `InvoiceController`.
2. Load the application with fee snapshots and pass the service-generated line items, subtotal, admin fee, and total to `resources/views/pdf/invoice.blade.php`.
3. Keep the existing invoice branding/logo and invoice number behavior unchanged.
4. Update Finance revenue to sum the actual snapshot total for paid applications rather than multiplying a fixed fee by paid count. Use the current fallback/snapshot creation path for legacy records.
5. Update the Finance payment table amount column to use each application’s actual snapshot total.

## Authorization and Navigation

- Keep the existing `admin.finance` route guard based on `payments.view`.
- Keep fee mutations guarded by `payments.verify`.
- Add a “Payment Fees” navigation/anchor link on Finance for users who can verify payments; do not expose CRUD controls to view-only users.
- Student routes remain restricted to the Student role as already implemented.

## Tests

1. Test fee seeding creates expected global/scoped defaults and is idempotent.
2. Test resolution selects exact Academic Year + School Level fees and falls back to global Admin Fee.
3. Test invalid scopes, duplicate fee types/scopes, negative amounts, and missing required scope validation.
4. Test Finance/Administrator can create, edit, deactivate, and delete eligible fees; Finance without `payments.verify` cannot mutate them.
5. Test Student payment step displays database values and calculates the correct subtotal/admin fee/total.
6. Test snapshot creation at step 4 and confirm later fee edits do not change the existing application total or PDF line items.
7. Test Finance metrics and payment table use actual snapshot totals.
8. Run the focused fee, Finance, Application, and invoice tests, then Pint and the full PHPUnit suite; run the frontend build for the Blade/Tailwind changes.

## Rollout Notes

- Run migrations before seeding defaults.
- Seed defaults once after deployment; avoid reseeding in a way that overwrites Finance-managed amounts.
- Existing applications without snapshots need a controlled lazy backfill when first viewed at payment/invoice time, with logging or tests confirming no hardcoded fallback remains.
- Existing paid applications require a one-time snapshot/backfill policy before revenue totals can be trusted. If historical fee data is unavailable, explicitly backfill them using the legacy 754,500 total and document that conversion in the migration/seeder.
