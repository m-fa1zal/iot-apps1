# Step-by-Step Guide: Deploy GitHub Project to Railway with MySQL

## 1) Deploy your GitHub repo to Railway

-   Log in to Railway → **New Project → Deploy from GitHub repo** → pick
    your repo.
-   The service builds automatically. If your app needs env vars, click
    **Add variables** before the first deploy.

## 2) Add a MySQL database on Railway

-   In the same project canvas, click **+ New → Database → MySQL →
    Deploy**.
-   Open the MySQL service → **Variables**. Railway exposes:
    -   `MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLPASSWORD`,
        `MYSQLDATABASE`, `MYSQL_URL`
-   You can also connect from outside Railway via TCP proxy.

## 3) Decide the database name

**Option A:** Use the default DB provisioned by Railway. Set
`DB_DATABASE` to `MYSQLDATABASE`.\
**Option B:** Create your own database name (e.g., `iot-apps1`).

Example SQL (via client/Workbench):

``` sql
CREATE DATABASE IF NOT EXISTS `iot-apps1`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

## 4) Wire your app service to the MySQL service (Reference Variables)

Add in **App service → Variables**:

``` env
DB_HOST=${{ mysql.MYSQLHOST }}
DB_PORT=${{ mysql.MYSQLPORT }}
DB_USERNAME=${{ mysql.MYSQLUSER }}
DB_PASSWORD=${{ mysql.MYSQLPASSWORD }}
DB_DATABASE=iot-apps1   # or ${{ mysql.MYSQLDATABASE }}
```

### Framework Examples

**Laravel:**

``` env
APP_KEY=...
DB_CONNECTION=mysql
DB_HOST=${{ mysql.MYSQLHOST }}
DB_PORT=${{ mysql.MYSQLPORT }}
DB_DATABASE=iot-apps1
DB_USERNAME=${{ mysql.MYSQLUSER }}
DB_PASSWORD=${{ mysql.MYSQLPASSWORD }}
LOG_CHANNEL=errorlog
```

**Node (Prisma/Sequelize):**

``` env
DATABASE_URL=${{ mysql.MYSQL_URL }}
# or discrete MYSQLHOST, MYSQLPORT, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE
```

**Django:**

``` env
DATABASE_URL=${{ mysql.MYSQL_URL }}
```

## 5) Run migrations automatically

Railway does not allow interactive shell. Use **Pre-Deploy Command**.

-   Laravel: `php artisan migrate --force`
-   Prisma: `npx prisma migrate deploy`
-   Django: `python manage.py migrate`

## 6) Give your app a public URL

App service → **Settings → Networking → Generate Domain**.

## 7) Troubleshooting

-   **`getaddrinfo ENOTFOUND`** → ensure
    `DB_HOST=${{ mysql.MYSQLHOST }}`\
-   **Connection refused** → confirm `DB_PORT` and MySQL service
    running\
-   **Migrations not run** → ensure Pre-Deploy commands are set

------------------------------------------------------------------------

## Quick Copy-Paste Blocks

**If `DB_DATABASE=iot-apps1`:**

``` env
DB_HOST=${{ mysql.MYSQLHOST }}
DB_PORT=${{ mysql.MYSQLPORT }}
DB_USERNAME=${{ mysql.MYSQLUSER }}
DB_PASSWORD=${{ mysql.MYSQLPASSWORD }}
DB_DATABASE=iot-apps1
```

**If using default DB:**

``` env
DB_HOST=${{ mysql.MYSQLHOST }}
DB_PORT=${{ mysql.MYSQLPORT }}
DB_USERNAME=${{ mysql.MYSQLUSER }}
DB_PASSWORD=${{ mysql.MYSQLPASSWORD }}
DB_DATABASE=${{ mysql.MYSQLDATABASE }}
```
