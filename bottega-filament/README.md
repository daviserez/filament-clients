# App to manage clients and services

# Development
To quickly install dependencies and run the app (need docker and docker compose installed):

```bash
docker run -it --rm --name bottega-filament -v ./:/app -w /app composer:latest bash --ignore-platform-reqs
./vendor/bin/sail up -d
./vendor/bin/sail php artisan key:generate
./vendor/bin/sail php artisan migrate:fresh --seed
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

## Deployment

> ⚠️ This procedure is not to be used as is. It's main purpose is to be
> a reminder for the differents steps used for the deployment.

Don't forget to update assets and pushing changes to the repo.

```bash
npm run build
```

Run the deploy script on the server. The script have commented instructions
for the first deployment that should be uncommected.

Update the `.env` file accordingly.

```bash
. deploy.sh`
```

By default, users cannot register. To enable this functionality, change the
env var `AUTH_CAN_REGISTER` to true.

# Migration script

To migrate old data, use `app:migrate-legacy` command.

If the database user has not the right to create a database (used as temp db to
migrate data), you should consider creating one manually and specifies it when the
script ask for. Add write rights to the db user for this temp database.

```bash
php artisan app:migrate-legacy legacy-sql-file.sql
```

where `legacy-sql-file.sql` is a dump of the legacy database.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
