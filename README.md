# MachSavelBundle

The MachSavelBundle adds support for a OAuth2 based authentication within your Symfony2 application. A flexible framework that is provided aims to handle 
most of the available OAuth2 Providers (e.g. Google, Facebook, Twitter). Currently, only Google OAuth2 is implemented but many of them will be implemented 
as soon as possible (if you want to, you can write it on your own and send us a patch).

Features include:

- Uses SSL protocol to communicate with OAuth2 Provider
- Supports [FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle) out-of-the-box
- Fully configurable, including User Entity's bindings for retrieving, creating and updating it

## Documentation

This bundle does not provide a documentation other than this file and the phpDoc tags inside all the files.

## Installation

### Step 1: Download MachSavelBundle

Use Symfony2 **vendors script**:

```
[MachSavelBundle]
    git=git://github.com/tiraeth/MachSavelBundle.git
    target=bundles/Mach/SavelBundle
```

``` bash
$ php bin/vendors install
```

### Step 2: Configure the Autoloader

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...
    'Mach' => __DIR__./../vendor/bundles',
));
```

### Step 3: Enable the bundle

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Mach\SavelBundle\MachSavelBundle(),
    );
}
```

### Step 4: Configure your routing

``` yaml
# app/config/routing.yml

MachSavelBundle:
    resource: "@MachSavelBundle/Resources/config/routing.yml"
    prefix:   /
```

### Step 5: Configure the bundle

``` yaml
# app/config/config.yml

# MachSavelBundle Configuration
mach_savel:
    security_provider: main
    default_redirect: _demo
    services:
        -                          # please mind this dash
            snufkin:            '' # class of this snufkin (e.g. Mach\SavelBundle\Snufkin\GoogleSnufkin)
            client_id:          '' # client id
            client_secret:      '' # client secret
            scope:              [] # scope of this service
            callback:
                route:          '' # name of callback route (should be set to MachSavelBundle_confirm)
                params:
                    service:    '' # name of service assigned to this snufkin (e.g. Google)
            bridge_service:     '' # id of bridge service (e.g. mach_savel.bridge.fos_user_bundle)
            user_class:         '' # user entity class
            binding:
                - { property: 'email', endpoint: 'user-info', path: 'email', isCreator: true }
                - { property: 'username', endpoint: 'user-info', path: 'email', isDiscriminator: true, isCreator: true }
```

Binding items have 5 pre-defined properties:

- *property*, that\'s the entity\'s property
- *endpoint*, that\'s the endpoint from which the data should be retrieved
- *path*, that\'s the path inside the data structure where the property value is set
- *isCreator*, defines if this property should be used to create the entity
- *isDiscriminator*, defines if this property should be used to find existing entity

Above configuration contains two example bindings that are used with GoogleSnufkin service.

## License

This bundle is under the MIT license. See the complete license in the bundle:

```
LICENSE
```

## Reporting an issue or a feature request

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/tiraeth/MachSavelBundle/issues).

When reporting a bug, it may be a good idea to reproduce it in a basic project
built using the [Symfony Standard Edition](https://github.com/symfony/symfony-standard)
to allow developers of the bundle to reproduce the issue by simply cloning it
and following some steps.