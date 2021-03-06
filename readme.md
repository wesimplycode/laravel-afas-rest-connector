## What this package does
This package integrates the AFAS REST API with Laravel with minimal setup.

## Installation
```
composer require we-simply-code/laravel-afas-rest-connector
```

After installation publish the config file
```
php artisan vendor:publish --provider="WeSimplyCode\LaravelAfasRestConnector\LaravelAfasRestConnectorServiceProvider"
```
In the config file you can add different connections to the AFAS profitServices.
One connection is added by default. If you will be using only one connection use the default connection.
If you will be using multiple connections use the default connection for the connection you will be using the most to make things easier.

Don't forget to add the config variables to you .env file.

## Usage
I assume you know how the AFAS profitServices work and what the different connectors do.
If not please take a look at the documentation for the AFAS REST API: https://help.afas.nl/help/NL/SE/App_Cnr_Rest_Api.htm?query=rest

#### GetConnector
With the GetConnector you can retrieve data from AFAS profitService.
After configuring your getConnectors for your connection you can use the like this:

```php
// This will give you the "contacts" getConnector for the default connection
Afas::getConnector('contacts')
```
