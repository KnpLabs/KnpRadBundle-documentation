Symfony 2.0 RAD Edition
=======================

Symfony2 with preconfigured `Knp:RadBundle`. Enhances Symfony2 to be more conventional,
rapid, clean and implicit.

RadBundle
---------

The core of the edition is `Knp:RadBundle`. This is a normal Symfony2 bundle, except
the fact, that it totally changes your Symfony2 application development flow.

Next section describes what this bundle changes in Symfony2.

Composer
--------

Composer now is the main part of Symfony2. All dependencies and bundles are managed by it.
You can define your project dependencies inside special `composer.json` file and load them by
calling `bin/vendors install` or `bin/vendors update`.

Hiding useless scripts and classes
----------------------------------

`app/AppKernel.php`, `app/AppCache.php` classes were been removed in favor of new `RadKernel`.
Now you just need to use predefined by `RadBundle` kernel in your front controllers:

``` php
<?php

use Symfony\Component\HttpFoundation\Request;
use Knp\Bundle\RadBundle\HttpKernel\RadKernel;

$loader = require(__DIR__.'/../vendor/.composer/autoload.php');
RadKernel::autoload($loader);

$kernel = new RadKernel('prod', false);
$kernel->loadClassCache();
//$kernel = new Knp\Bundle\RadBundle\AppCache($kernel);
$kernel->handle(Request::createFromGlobals())->send();
```

That's it. This `RadKernel` follows simple conventions with which you can configure/maintain
project without the need to maintain complex php class.

Project structure changes
-------------------------

- `app/cache` and `app/logs` have been moved to the root.
- new `config` folder have been created

New `config` folder holds all the project configuration:

- `config/project.yml` holds project-wide configuration such as project name, list of bundles
  and project parameters.
- `config/routing/*.yml` holds routing configurations.
- `config/bundles/*.yml` holds bundles configurations, including your applications. Every
  bundle has appropriate configuration file, that lives here. For example, `FOSRestBundle` will
  have `config/bundles/fos_rest.yml` config file. Configuration for different environemnts of
  this bundle live here.

Lets look at the default `config/project.yml`:

``` yaml
name: Acme\Hello

all:
    apps:
        - Frontend

    bundles:
        Symfony\Bundle\FrameworkBundle\FrameworkBundle:     -
        Symfony\Bundle\SecurityBundle\SecurityBundle:       -
        Symfony\Bundle\TwigBundle\TwigBundle:               -
        Symfony\Bundle\MonologBundle\MonologBundle:         -
        Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle: -
        Symfony\Bundle\AsseticBundle\AsseticBundle:         -
        Knp\Bundle\RadBundle\KnpRadBundle:                  -

    parameters:
        database_driver:   pdo_mysql
        database_host:     localhost
        database_port:     -
        database_name:     symfony
        database_user:     root
        database_password: -

        mailer_transport:  smtp
        mailer_host:       localhost
        mailer_user:       -
        mailer_password:   -

        locale:            en
        secret:            ThisTokenIsNotSoSecretChangeIt

dev:
    bundles:
        Symfony\Bundle\WebProfilerBundle\WebProfilerBundle:  -

test:
    bundles:
        Symfony\Bundle\WebProfilerBundle\WebProfilerBundle:  -
```

`name` defines project namespace of your project. If you want to make your project namespace path
smaller, you can simply avoid organization name in it:

``` yaml
name: Hello
```

Then, there are 3 sections in this config file. Each section defines project configuration for
different environments. `all` section configuration always will be loaded before any environment.

Every environment configuration has 3 main sections:

- `apps` defines list of your application bundles (`Frontend`)
- `bundles` defines list of 3rd-party bundles, that you're using inside your project (in specific
  environment)
- `parameters` defines project parameters. Same as old `parameters.ini` file

Now lets look at specific bundle configuration, `config/bundles/framework.yml`:

``` yaml
all:
    #esi:             -
    #translator:      { fallback: %locale% }
    secret:          %secret%
    charset:         UTF-8
    form:            true
    csrf_protection: true
    validation:      { enable_annotations: true }
    templating:      { engines: ['twig'] } #assets_version: SomeVersionScheme
    session:
        default_locale: %locale%
        auto_start:     true

prod:
    router:   { resource: "%kernel.root_dir%/routing/prod.yml" }
    profiler: { only_exceptions: false }

dev:
    router:   { resource: "%kernel.root_dir%/routing/dev.yml" }
    profiler: { only_exceptions: false }

test:
    router:   { resource: "%kernel.root_dir%/routing/test.yml" }
    profiler: { only_exceptions: false }
    test: -
    session:
        storage_id: session.storage.filesystem
```

This file defines `FrameworkBundle` configuration for different environments. Root keys
of the config works same as for the `config/project.yml` - `all` parameters will be loaded
for every environment and specific environment keys adds specific declarations to it.

As you might see, every environment in this edition have it's own routing configuration file
inside `config/routing/*.yml`. It's the same old Symfony2 routing configuration files.

Application bundles structure changes
-------------------------------------

Application bundles are very special type of Symfony2 bundles. Because they will be used
only for one single project, they don't need to share same development requirements and rules, 
that common bundles do. We can avoid most of the explicit classes and configurations by simply
adding conventions on top of them. And that's exactly what `RadBundle` does.

Every application bundle (name defines under `apps` in `config/project.yml`) with `RadBundle`
shares same directory structure:

``` bash
src/Acme
└── Hello
    └── Frontend
        ├── Controller
        │   └── DefaultController.php
        ├── Tests
        │   └── Controller
        │       └── DefaultControllerTest.php
        ├── config
        │   ├── routing.yml
        │   │── services.yml
        │   └── services.xml
        ├── public
        │   └── jquery-1.7.1.min.js
        └── views
            ├── Default
            │   └── index.html.twig
            └── base.html.twig
```

`Frontend` is a bundle name. As it's private bundle, that will be used only inside your project
it can avoid `Bundle` suffix requirement altogether. Also, application bundles don't have
organization prefix in their names. It means, that you can define those bundle controllers inside
views or routings as `Frontend:Default:index`.

`config` holds all your application configurations, including routing and DIC. And those
configurations will be autoloaded. Yup, no need to include `routing.yml` in your project
`config/routing/routing.yml` or to create extension class just to load `services.yml` (or
services.xml). The only requirement - they should be named `routing.yml` OR `services.(yml|xml)`.

- `config/routing.yml` - defines bundle routes. Will be autoloaded by `RadKernel` for you.
- `config/services.(yml|xml)` - defines bundle services. Will be autoloaded by `RadKernel` for you.

`public` holds all your public `*.css`, `*.js` and `*.png` files. This folder will be copied
(symlinked) with `assets:install` command.

`views` holds your application views.

Application bundle configuration
--------------------------------

Ok, but if we don't have extension, then how to provide project-wide parameters for our application?
Application bundles implicitly instanciate `ConventionalExtension`, which will automatically add
configuration of `config/bundles/YOUR_APPLICATION_NAME.yml` (`config/bundles/frontend.yml` in our
case) into DIC with `YOUR_APPLICATION_NAME.` prefix (`frontend.` in our case). So,
`config/bundles/frontend.yml`:

``` yaml
all:
    name_pattern: <em>%s</em>
```

will become `frontend.name_pattern` inside DIC for every environment. If you want to use different
value for specific environment, add appropriate key:

``` yaml
all:
    name_pattern: <em>%s</em>
dev:
    name_pattern: <strong>%s</strong>
```

View rendering inside controller
--------------------------------

There's no need to specify view name in controller action anymore. You can just return array of view
data:

``` php
<?php

namespace Acme\Hello\Frontend\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function index($name)
    {
        return array('name' => $name);
    }
}
```

Appropriate view (`views/CONTROLLER_NAME/ACTION_NAME._FORMAT.twig`) will be loaded for you.
