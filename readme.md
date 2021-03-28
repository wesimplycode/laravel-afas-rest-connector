## What this package does
This package integrates the AFAS REST API with Laravel with a minimal setup.

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
            * [Where](#where)
            * [orWhere](#orwhere)
      * [Execute](#execute)
      * [Generating URL](#generating-url)
      * [Inspecting the where filter](#inspecting-the-where-filter)
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
The getConnector supports the simple filter, or the json filter.
After configuring your getConnectors for your connection you can use the like this:

```php
// This will give you the "contacts" getConnector for the default connection
Afas::getConnector('contacts');

// This will give you the "contacts" getConnector for a different connection
Afas::getConnector('contacts', 'differentConnectionName');
```

#### Filters
You can apply filters on getConnectors to retrieve more specific data.
There is no specific order to apply filters. You can chain as many filters as you want except for the ```take()``` and ```skip()``` filter. Those can only be used once per request.

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
Field names must be exact the same as they are in AFAS profitServices.
```php
// This will sort the results ascending by the field 'Name'
Afas::getConnector('contacts')->sortOnField('Name');

// Add true as second parameter to sort the results descending
// This will sort the results descending by the field 'Name'
Afas::getConnector('contacts')->sortOnField('Name', true);
```

###### Where
If you want to get specific results from the getConnector you can use the ```where()``` filter (records must match all criterion). It is recommended to always use the ```where()```
filter with the getConnector as it enhances the performance and only gives you the results you need.

All AFAS Profit filters for the ```where()``` filter are available. The filters are listed in the config file. You can use them in their symbol form, or their text form.

**By default, the getConnector uses the simple filter. To enable the jsonFilter you can pass ```true``` as second parameter to the getConnector.**
```php
// The where() filter accepts the field type as first parameter, filter type as second and what the results should be filtered on as third
// Get only the contacts of type Person (simple filter)
Afas::getConnector('contacts')->where('type', '=', 'Person');

// Get only the contacts of type Person (json filter)
Afas::getConnector('contacts', true)->where('type', '=', 'Person');

// You can chain as much where filters as needed
// Get only the contacts from the Netherlands who are organizations
Afas::getConnector('contacts')
    ->where('country', '=', 'Netherlands')
    ->where('type', '=', 'Organization');
```

###### orWhere
You can use the ```orWhere()``` filter to add another where clause to the filter (records must match at least one criterion). Please check out the official docs how this works
```php
// The orWhere() filter accepts the field type as first parameter, filter type as second and what the results should be filtered on as third
// Get the contacts of type Person or Organization
Afas::getConnector('contacts')
    ->where('type', '=', 'Person')
    ->orWhere('type', '=', 'Organization');

// Get only the contacts from the Netherlands or Germany who are organizations (json filter)
Afas::getConnector('contacts', true)
    ->where('type', '=', 'Organization')
    ->where('country', '=', 'Netherlands')
    ->orWhere('type', '=', 'organization')
    ->where('country', '=', 'Germany');
```

**Sometimes the simple filter isn't enough to query specific results. Enable the jsonFilter when doing advanced queries. The jsonFilter and the simple filter don't always return the same results!**

#### Execute
The ```execute()``` method is used when you have configured the connector accordingly to make the call to the AFAS profitServices.
```php
// Execute the call. This will retrieve 100 contacts from the AFAS profitServices
Afas::getConnector('contacts')->execute();

// Retrieve 10 contacts
Afas::getConnector('contacts')
    ->take(10)
    ->execute();

// Retrieve 100 contacts but skip the first
Afas::getConnector('contacts')
    ->skip(1)
    ->execute();

// Retrieve 10 contacts and skip the first
Afas::getConnector('contacts')
    ->skip(1)
    ->take(10)
    ->execute();
```

This package uses Laravel's wrapper around Guzzle to make http calls.
That means that after we call the ```execute()``` method, we can use the methods Laravel provides to inspect the response.
Example:
```php
// This will return the request status
Afas::getConnector('contacts')
    ->execute()
    ->status();

// This will return the response in JSON
Afas::getConnector('contacts')
    ->execute()
    ->json();
```
Check out the documentation for the Laravel http client: https://laravel.com/docs/8.x/http-client#introduction

#### Generating URL
If you want to inspect the URL that is being generated by the connector you can call the ```getUrl()```
method instead of the ```execute()``` method after configuring the connector.

```php
// Both of these will return the generated URL by the connector (you can use this directly to make a call in something like Postman)
Afas::getConnector('contacts')->getUrl();

Afas::getConnector('contacts')
    ->take(10)
    ->where('Type', '=', 'Person')
    ->getUrl();
```

#### Inspecting the where filter
If you want to inspect the json from the where filter you can call the ```getJsonFilter()``` method instead of the ```execute()``` method after configuring the getConnector.
```php
Afas::getConnector('contacts')
    ->take(10)
    ->where('Type', '=', 'Person')
    ->getJsonFilter();
```
This method won't return the take, skip and sortOnField filter.

## Credits
Sunil Kisoensingh

## License
The MIT License (MIT). Please see [License File](LICENSE) for more information.
