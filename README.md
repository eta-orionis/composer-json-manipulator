# Manipulate composer.json with a fluent API

- load `composer.json` as an object
- manipulate using fluent API
- save it back to a `composer.json` file

## Install

```bash
composer require eta-orionis/composer-json-manipulator
```

## Usage

```php
namespace App;

use EtaOrionis\ComposerJsonManipulator\ComposerJson;

class SomeClass
{
    public function run(): void
    {
        $composerJson = ComposerJson::fromFile(getcwd() . '/composer.json');

        // Add a PSR-4 namespace
        $autoLoad = $composerJson->getAutoload();
        $autoLoad['psr-4']['Cool\\Stuff\\'] = './lib/';
        
        $composerJson
            ->setAutoload($autoLoad)
            ->save(getcwd() . '/composer.json');
    }
}
```