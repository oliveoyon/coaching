# Coaching CMS Logic Notes

Last updated: 2026-05-15

This file records the core business logic and implementation decisions already made in the system. It is meant as an internal reminder before future modules or refactors.

## 1. Data baseline

- The system is currently intended to run from real master data, not demo data.
- Safe baseline data:
  - roles
  - permissions
  - users
  - teachers
  - classes
  - subjects
  - batches
  - batch teacher assignments
- `MasterDataBackupSeeder` exists as a backup for the current master setup.
- Operational data can be cleared and rebuilt when needed:
  - students
  - enrollments
  - fees
  - payments
  - attendance
  - expenses
  - admission requests
  - settlements

## 2. User and teacher account flow

- `User Management` is for:
  - Super Admin
  - Admin
  - Accounts
- `Teacher Management` is separate.
- Creating a teacher from `Teacher Management` creates:
  - user login account
  - teacher profile
  - `Teacher` role assignment
- Users can log in with:
  - username + password
  - email + password

## 3. Student identity model

- One student should have only one student record.
- One student can join many batches through many enrollments.
- Student code belongs to the student, not to the batch.
- Batch joining belongs to `enrollments`.
- Online admission should create an admission request first.
- Admin approval decides:
  - create new student
  - or link to existing student

## 4. Enrollment model

- Enrollment is the operational link between:
  - student
  - batch
  - billing
  - attendance
  - payment history
- Main enrollment statuses in use:
  - `active`
  - `completed`
  - `withdrawn`
- `completed` is used for a natural end of study or promotion.
- `withdrawn` is used when a student leaves before the normal flow.

## 5. Promotion / re-enrollment model

- Promotion is intentionally light.
- No heavy academic-year engine has been added.
- Current solution: `Promotion Center`

### Practical rule

- Student stays the same.
- Old enrollment is closed.
- New enrollment is created.
- Student class is updated to the target batch class.

### Promotion flow

1. Create next batch first.
2. Set up batch fees for the target batch first.
3. Open `Promotion Center`.
4. Choose current batch.
5. Select students.
6. Choose target batch.
7. Choose new start date.
8. Promote selected students.

### Promotion result

- old enrollment status becomes `completed`
- old enrollment end date is set
- new active enrollment is created
- student `class_id` is updated to the target batch class

### Important financial rule

- Old dues remain on old enrollment.
- New billing starts from new enrollment only.
- Old fee history is not merged silently into the new class.

## 6. Batch and schedule model

- Batches support multiple schedule rows.
- One batch can have different time slots on different days.
- Schedule is stored in `schedule_slots`.
- Legacy time/day fields are still filled for compatibility.

## 7. Fee setup model

- Fee setup is batch-wise.
- Fee setup should exist before enrollment.
- If a batch is intentionally free, fee rows must still be configured with amount `0`.
- `No fee setup` means incomplete configuration.
- `Configured with 0 amount` means intentionally free.

### Fee types

Typical fee frequencies:

- `monthly`
  - tuition
- `one_time`
  - admission
- `manual`
  - exam or occasional fee

## 8. Batch fee behavior

The fee system is dynamic and month-aware.

### Supported behavior

- different fee setup per batch
- fee can start from a selected month
- fee can end at a selected month
- paused month for a whole batch
- special month amount for a particular month
- free fee item with `0` amount
- future fee update without rewriting previous months

### Implemented mechanisms

- `batch_fees`
  - core fee rows
- `batch_billing_breaks`
  - whole-batch paused billing months
- `batch_fee_month_overrides`
  - special one-month amount override
- `enrollment_fee_adjustments`
  - student-specific discount / waiver

### Practical examples

- July coaching closed:
  - add paused month `2026-07`
  - monthly tuition is skipped for that batch in July
- August discounted:
  - add a special month amount for `2026-08`
  - example: tuition becomes `1200`
- September fee change:
  - update fee with `Apply from Month = 2026-09`
  - previous months remain unchanged

## 9. Student-specific discount / waiver

- Discount and waiver are enrollment-based.
- This is important because one student may have:
  - one discount in one batch
  - different discount in another batch
- Adjustment can be:
  - fixed amount
  - percent
- Adjustment can be limited by month range.

## 10. Payment collection model

- Collection is student-based and batch-wise.
- Within a batch, overdue monthly fees are shown month-wise.
- Example:
  - Mar 2026 tuition
  - Apr 2026 tuition
  - May 2026 tuition
- Collection page no longer treats all batches as one save target.
- Each batch is collected separately.

### Current collection behavior

- fully paid items are hidden from collection
- batches with nothing due are hidden
- all batch panels are collapsed by default
- payment save is individual per batch

## 11. Payment approval and distribution

- Approved payments create teacher distribution.
- Distribution is separate from settlement.
- Distribution says who earned the money.
- Settlement says who has actually been paid.

### Collector logic

- Payment records track who collected the money.
- This is important for liability/payable tracking.

## 12. Teacher payable / settlement model

- Distribution and settlement are separate layers.

### Distribution

- logical earning share
- based on approved payment

### Settlement

- actual payout to teacher
- reduces outstanding payable

### Result

The system can answer:

- who collected the payment
- who earned from it
- how much was settled
- how much is still payable

## 13. Attendance model

Attendance is designed as one system with three modes:

- face
- QR / barcode
- manual

### Important design rule

- teacher experience must stay smooth
- fallback must always exist
- face is not the only path

### Current face attendance

- automatic face matching in browser is enabled
- it is intended mainly for:
  - one student at a time
  - or very small clear capture flow
- mobile-first feedback exists:
  - clear success signal
  - next student flow
  - fast mode switching

### Attendance sessions

- session is per batch + date
- remaining pending students become absent when session is completed

## 14. Face registration sources

Face registration can come from:

- public online admission form
- offline student create/edit from office panel

### Offline admission

- office can:
  - open camera
  - capture face
  - or upload image
- this image also supports future face attendance

## 15. Online admission links

- Admission links are batch-based.
- Admission request remains separate until admin approval.
- Duplicate student risk still needs careful admin review if multiple requests come for the same person.

## 16. Student profile logic

- Student profile is an internal admin/teacher page, not a student login portal.
- It shows:
  - student identity
  - active and previous enrollments
  - fees
  - payments
  - attendance
  - admissions
- It has been made more compact and less auto-loaded.

## 17. Enrollment create safeguards

- Long student dropdown was removed.
- Enrollment is search-first.
- Student result list hides after selection.
- Batch fee setup warning is visible in UI.
- Save is blocked if fee setup is missing.

## 18. Batch fee setup UI direction

- Billing pages should not be wide table-heavy screens.
- More practical laptop-friendly direction:
  - compact cards
  - short forms
  - grouped actions
- Batch list itself should not show a misleading single fee column.
- Billing lives under `Batch Fee Setup`.

## 19. Root/login/public pages

- `/login` acts as the public landing + login page.
- `/` redirects:
  - guest -> `/login`
  - authenticated user -> dashboard
- Public pages are intentionally simple and not overloaded.

## 20. Current practical manual entry order

1. User Management for Admin and Accounts
2. Teacher Management
3. Class Management
4. Subject Management
5. Batch Management
6. Fee Types
7. Batch Fee Setup
8. Student Management
9. Enrollment Management
10. Payment Collection
11. Teacher Settlements
12. Expense Management

If online admission is used:

1. Batch Admission Link
2. Student submits form
3. Admin approves request
4. Enrollment
5. Payment

## 21. Current known practical limitations

- Promotion Center currently shows active batches, not a separate next-year batch group.
- Old dues are not auto-carried into the new enrollment by design.
- Online admission duplicate protection can still be improved further.
- Face attendance works best in controlled real-life conditions, not full-class automatic sweep.

## 22. Design principle going forward

- strong but not heavy
- practical for office and teachers
- keep history instead of rewriting it
- separate academic movement from financial history
- add explicit setup for special cases instead of hidden magic
