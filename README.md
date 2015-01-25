# CakePHP AWS Utilities

AWSUtils provides a small set of wrappers and harnesses to integrate AWS services directly into
your CakePHP applications

## Requirements

The master branch has the following requirements:

* CakePHP 2.2.0 or greater.
* PHP 5.3.0 or greater.

## Features

* [DynamoDB][dynamodb] session handler.

## Installation

* Clone/Copy the files in this directory into `app/Plugin/AwsUtils`
* Ensure the plugin is loaded in `app/Config/bootstrap.php` by calling `CakePlugin::load('AwsUtils');`

### Using Composer

Ensure `require` is present in `composer.json`. This will install the plugin into `Plugin/AwsUtils`:

```
{
    "require": {
        "cuppett/cakephp-aws_utils": "1.0.*"
    },
    "extra":
	{
	    "installer-paths":
	    {
	        "app/Plugin/AwsUtils": ["cuppett/cakephp-aws_utils"]
	    }
	}       
}
```

## Quick Example

### Integrating the session handler into core.php

```php
use Aws\Common\Enum\Region;

$aws = array(
    'includes' => array(
        '_aws'
    ),
    'services' => array(
        'default_settings' => array(
            'params' => array(
                'region' => Region::US_EAST_1
            )
        )
    )
);

Configure::write('Session', array(
    'defaults' => 'database',
    'timeout' => 60,
    'handler' => array(
        'engine' => 'AwsUtils.DynamoDBSession',
        'aws' => $aws
    ),
    /* Avoid gc from web-app, manually sweep/clean later */
    'ini' => array('session.gc_probability' => 0)
));
```

## Reporting issues

If you have a problem with AwsUtils please open an issue on [GitHub][issues].

[dynamodb]: http://aws.amazon.com/dynamodb/
[issues]: https://github.com/cuppett/cakephp-aws_utils/issues