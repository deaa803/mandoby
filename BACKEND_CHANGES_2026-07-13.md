# Backend changes

- Removed visible order IDs and numeric countdown values from notification titles and bodies.
- Kept internal IDs inside notification `data` for app navigation.
- Added `orders:check-eta` command and scheduled it every minute.
- Added ETA warning and late notification timestamps to prevent duplicate notifications.
- Added 3D model completion notifications from the actual generation job and controller update flow.
- Removed company/store installment endpoints and installment response methods.

## Run after replacing the project

```bash
php artisan migrate
php artisan optimize:clear
php artisan schedule:work
php artisan queue:work
```

Production cron should run Laravel scheduler every minute.
