Petfinder PHP API
=================

PHP Library to interact with Petfinder.com API

Installation
============
You can install this class via composer. Add to your composer.json file:

```
"required": {
	"pickupman/petfinder": "dev-master"
}
```

Then run `composer update` from the command line in your projects root folder. Then instantiate the class by:

```
$petfinder = new \Pickupman\Petfinder\Petfinder(['api_key' => PETFINDER_API_KEY, 'api_pass' => PETFINDER_API_PASSWORD);
``` 
