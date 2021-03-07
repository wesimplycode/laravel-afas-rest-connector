## What this package does
This package integrates the AFAS REST API with Laravel with minimal setup.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/we-simply-code/laravel-afas-rest-connector)](https://packagist.org/packages/we-simply-code/laravel-afas-rest-connector)

## Table of Contents  
<!--ts-->
   * [Installation](#installation)
   * [Usage](#usage)
      * [GetConnector](#getconnector)
      * [Filters](#filters)
         * [Take](#take)
         * [Skip](#skip)
         * [SortOnField](#sortonfield)
      * [Execute](#execute)
      * [Multiple connectors](#multiple-connectors-on-the-same-connection)
   * [Credits](#credits)
   * [License](#license)
<!--te-->

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

This package ships with a facade to access the different connectors easily.
After retrieving your connector you can apply filters to it or add data to it and then call the ```execute()``` method to make the call to AFAS profitServices.

#### GetConnector
With the GetConnector you can retrieve data from AFAS profitService.
After configuring your getConnectors for your connection you can use the like this:

```php
// This will give you the "contacts" getConnector for the default connection
Afas::getConnector('contacts');

// This will give you the "contacts" getConnector for a different connection
Afas::getConnector('contacts', 'differentConnectionName');
```

#### Filters
You can apply filters on getConnectors to retrieve more specific data.
There is no specific order to apply filters. You can chain as many filters as you want except for the ```take()``` and ```skip()``` filter. Those can only be use once per request.

###### Take
By default, the profitServices return 100 results. You can adjust the amount of results by adding the ```take()``` filter.
```php
// This will add the take filter to the connector with an amount of 10
Afas::getConnector('contacts')->take(10);
```

###### Skip
You can skip results by adding the ```skip()``` filter.
```php
// This will add the skip filter to the connector with an amount of 10
Afas::getConnector('contacts')->skip(10);
```

###### SortOnField
Sort the results on any field. By default, the results will be ascending but with and extra parameter you can change that.
```php
// This will sort the results ascending by the field 'Name'
Afas::getConnector('contacts')->sortOnField('Name');

// This will sort the results descending by the field 'Name'
Afas::getConnector('contacts')->sortOnField('Name', true);
```

#### Execute
The ```execute()``` method is used when you have configured the connector accordingly to make the call to the AFAS profitServices.
```php
// Execute the call. This will retrieve 100 contacts from the AFAS profitServices
Afas::getConnector('contacts')->execute();

// Retrieve 10 contacts
Afas::getConnector('contacts')->take(10)->execute();

// Retrieve 100 contacts but skip the first
Afas::getConnector('contacts')->skip(1)->execute();

// Retrieve 10 contacts and skip the first
Afas::getConnector('contacts')->skip(1)->take(10)->execute();
```

This package uses Laravel's wrapper around Guzzle to make http calls.
That means that after we call the ```execute()``` method, we can use the methods Laravel provides to inspect the response.
Example:
```php
// This will return the request status
Afas::getConnector('contacts')->execute()->status();

// This will return the response in JSON
Afas::getConnector('contacts')->execute()->json();
```
Check out the documentation for the Laravel http client: https://laravel.com/docs/8.x/http-client#introduction

#### Multiple connectors on the same connection
When you want to retrieve data from multiple connectors on the same connection you can retrieve an instance of a connection and make calls with different connectors on the same connection.
```php
// Retrieve an instance of the default connection
$connection = Afas::connection();

// Retrieve an instance of a different connection
$connection = Afas::connection('differentConnectionName');

/*
* Use different getConnectors to retrieve data on the same connection
*/
$contacts = $connection->getConnector('contacts')->take(1)->execute()->json();
$articles = $connection->getConnector('articles')->take(1)->skip(1)->execute()->json();
```

## Credits
Sunil Kisoensingh

## License
The MIT License (MIT). Please see [License File](LICENSE) for more information.
