# Parnera Champions Trophy

Cricket tournament website in Laravel: registration, auction, live score, scorecards, points table and admin panel.

## What you need
PHP 8.2 or newer, Composer, and MySQL (or the built-in SQLite). XAMPP or Laragon on Windows is fine.
PHP extensions: pdo_mysql or pdo_sqlite, mbstring, fileinfo, openssl.

## Install

1. Create a fresh Laravel 12 project:

       composer create-project laravel/laravel:^12.0 parnera

2. Copy the folders `app`, `database`, `public`, `resources` and `routes` from this package into the new
   `parnera` folder. Choose **Replace** when asked.

3. Database. SQLite works with no change. For MySQL, create a database called `parnera`, then edit `.env`:

       DB_CONNECTION=mysql
       DB_HOST=127.0.0.1
       DB_PORT=3306
       DB_DATABASE=parnera
       DB_USERNAME=root
       DB_PASSWORD=

   Also set `APP_NAME="Parnera Champions Trophy"` if you like. Indian time is already set by the app.

4. Run inside the `parnera` folder:

       php artisan migrate
       php artisan storage:link
       php artisan admin:create
       php artisan serve

5. Website: http://127.0.0.1:8000  Admin: http://127.0.0.1:8000/admin/login

If photos fail to upload, raise `upload_max_filesize` and `post_max_size` in php.ini to at least 10M.

## How to run the tournament
1. **Settings**: check title, date, venue, fee, UPI ID, phone, auction points.
2. Players fill the **Registration** form. You approve them in **Players**.
3. Add the 8 **Teams**.
4. **Auction**: start a player, tap a team to bid, press SOLD. Show `/auction` on a projector.
5. Captains or retained players: assign a team in **Players > Edit**.
6. **Matches**: add a match, press **Score**, record the toss, then tap runs ball by ball.
   `/live` updates on every phone automatically. Use **Undo** for mistakes.

## Phone app
The website is also an installable app (a "web app"). It is not a Play Store or App Store app.

- Installing needs the site online with **HTTPS**. On your own computer it only works at `127.0.0.1` / `localhost`.
- **Android (Chrome):** open the site and tap **Install**, or menu > **Add to Home screen** / **Install app**.
- **iPhone (Safari):** tap **Share** > **Add to Home Screen**.
- The app needs internet. Without it, an offline message is shown.
- App icons are `public/images/icon-*.png` and `apple-touch-icon.png`. Changing the logo in Settings does not change them.
- If your server does not serve `manifest.webmanifest` correctly, set its type to `application/manifest+json`.

## Going online
Point the domain to the `public` folder, set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://yourdomain`,
use HTTPS, then run `php artisan migrate --force`, `php artisan storage:link` and `php artisan optimize`.
Take a database backup before the tournament days.

## Rules built in
- Points: win 2, tie or no result 1. Ranking by points, then net run rate (League matches only).
- A team cannot bid so high that it cannot fill its minimum squad at base price.
- Wide and no ball add 1 run; free hit after a no ball; no bowler bowls two overs in a row.
- Not included: DLS, automatic super over, penalty runs, per-bowler over limit, online payment gateway.

## Test the scoring logic

    php tests-standalone/run.php
