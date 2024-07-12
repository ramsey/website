# Geo IP Data

As part of analytics collection, this project uses IP address geolocation tools
to look up locale information associated with client IP addresses.

> [!NOTE]
> The client IP address is *never* stored. It is used only for geolocation
> lookup. The data that is stored (including city, country, latitude, and
> longitude values) is not linked in any way to personally identifiable
> information, and is at best a guess about the general area of the IP address.
>
> To provide a rough count of "unique" visits, the IP address is hashed with the
> user agent string, and this hash is stored in the database.

This project uses the [MaxMind](https://www.maxmind.com) geolocation tools. To
run things locally, the MaxMind database should not be necessary. However, if
you wish to download it for testing, you may do so by signing up for a free
account at [maxmind.com](https://www.maxmind.com) and
[generate a license key](https://www.maxmind.com/en/accounts/current/license-key).

Next, create `.env.local` and/or `.env.test.local` and add your license key:

``` shell
MAXMIND_LICENSE_KEY='Your license key goes here.'
```

By default, local `dev` and `test` environments are configured to use the
`App\Service\Analytics\NoOpProvider` for tracking analytics. As its name
suggests, this provider takes no operation. That is, it doesn't attempt to
record/log any page views in the database or send data to any third-party
services. To use the same providers as production, find the following line for
`test` or `dev` in `config/services.yaml` and comment it out:

```yaml
App\Service\Analytics\AnalyticsService: '@App\Service\Analytics\NoOpProvider'
```

## Downloading/updating the geo IP database

Before you can use the geo IP functionality locally, you'll need to download or
update the geo IP database. You may do this by running:

``` shell
php bin/console geoip2:update
```

This command also runs in production before every deployment.
