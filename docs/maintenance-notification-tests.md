# Manual Test Scenarios — Maintenance Service Notifications

These scenarios validate when `NotificationService` records are created from maintenance + record data.

## Assumptions
- Notification triggers when `finish_km - maintenance.mileage >= 10,000`.
- Only maintenances with `isServiceRoutine = 1` are considered.
- Maintenance must be `COMPLETED`.
- Notifications are stored with `read = 0` and appear in the navbar service bell.

## Scenario A — Routine service, threshold reached (should create)
1. Create maintenance:
   - `isServiceRoutine = 1`
   - `status = COMPLETED`
   - `mileage = 10,000`
2. Create record (trip):
   - `finish_km = 20,500`
3. Expected:
   - `NotificationService` row created (`read = 0`)
   - Navbar service bell count increases

## Scenario B — Routine service, below threshold (should NOT create)
1. Create maintenance:
   - `isServiceRoutine = 1`
   - `status = COMPLETED`
   - `mileage = 10,000`
2. Create record (trip):
   - `finish_km = 19,000`
3. Expected:
   - No notification created

## Scenario C — Non-routine service (should NOT create)
1. Create maintenance:
   - `isServiceRoutine = 0`
   - `status = COMPLETED`
   - `mileage = 10,000`
2. Create record (trip):
   - `finish_km = 25,000`
3. Expected:
   - No notification created

## Scenario D — No completed routine maintenance (should NOT create)
1. Ensure no completed routine maintenance exists for the vehicle.
2. Create record (trip):
   - `finish_km = any`
3. Expected:
   - No notification created

## Update behavior (revised)
- If an unread notification already exists for the same rom_km, it will be **updated** with the latest 	o_km`n  when newer records are added, as long as the routine service anchor remains the same.

