# Deployment Guide — 5050 Logistics

Checklist for taking this app to production.

## 1. Choose a host

| Option | Best for | Notes |
|---|---|---|
| **Laravel Cloud** | Easiest | Managed DB, cache, queues. Deploys straight from GitHub. |
| **VPS** (DigitalOcean, Hetzner) | Control + cheap | Needs nginx, PHP-FPM, MySQL, supervisor for queues. |
| **Shared hosting (cPanel)** | Budget | Must support PHP 8.4; manual deploy via FTP/Git pull. |

## 2. Production `.env`

```env
APP_NAME="5050 Logistics"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_auth_prod
DB_USERNAME=...
DB_PASSWORD=...

MAIL_MAILER=...        # resend / ses / smtp — see below
MAIL_FROM_ADDRESS="no-reply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"

PAYSTACK_PUBLIC_KEY=pk_live_...
PAYSTACK_SECRET_KEY=sk_live_...

QUEUE_CONNECTION=database   # or redis
```

## 3. Email — Resend (free tier, recommended)

1. Sign up at [resend.com](https://resend.com) (free: 100 emails/day, 3,000/month)
2. Verify your domain `fifyfifylogistics.com` (add the DKIM/SPF records Resend gives you to Hostinger DNS)
3. Create an API key and set in `.env`:

```env
MAIL_MAILER=resend
RESEND_API_KEY=re_xxxxxxxxxxxx
MAIL_FROM_ADDRESS="no-reply@fifyfifylogistics.com"
MAIL_FROM_NAME="5050 Logistics"
```

> For testing before domain verification, Resend allows `onboarding@resend.dev` as sender.

Alternatives: `MAIL_MAILER=ses` (AWS SES) or `MAIL_MAILER=smtp` with any mailbox credentials.

## 4. Deploy steps

### Option A — automated (Hostinger KVM VPS, Ubuntu 24.04)

```bash
# On the VPS, as root:
export DB_PASSWORD='your-strong-db-password'
bash deploy.sh        # or: curl -fsSL <raw-github-url>/deploy.sh | bash
```

`deploy.sh` (in repo root) handles: packages, PHP 8.4, Composer, MySQL DB, git clone/pull, `.env` production values, migrations, caches, nginx vhost, firewall, Let's Encrypt SSL, and the scheduler cron.

### Option B — manual

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

- Point the web root at `public/`
- Run `php artisan queue:work` (or Laravel Cloud/Forge worker) for notifications
- Schedule: `* * * * * php artisan schedule:run` (cron)

## 5. Go-live checklist

- [ ] `APP_DEBUG=false`, `APP_ENV=production`
- [ ] HTTPS enabled (Let's Encrypt)
- [ ] Paystack **live** keys + webhook URL set in Paystack dashboard → `https://your-domain.com/payments/callback`
- [ ] Email provider verified, test password reset + shipment notification
- [ ] Database backups scheduled (daily dump)
- [ ] `dev@example.com` admin account removed or password rotated
- [ ] Run `php artisan test` in CI (GitHub Actions runs on every push)
