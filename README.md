# Web Components Rendering

## Usage option 1 (TypoScript only)

```typoscript
page.10.variables.logo = WEBCOMPONENT
page.10.variables.logo {
  tagName = my-logo
  properties {
    href.typolink.parameter = 1
    href.typolink.returnLast = url
    alt = This is my logo
    src.cObject = IMG_RESOURCE
    src.cObject.file = fileadmin/mylogo.png
  }
}
```

Output: `<my-logo href="http://www.example.com" alt="This is my logo" src="/fileadmin/mylogo.png"></my-logo>`

## Usage option 2 (with dataProvider)

```typoscript
page.10.variables.socialLinks = WEBCOMPONENT
page.10.variables.socialLinks.dataProvider = Vendor\MyExtension\DataProvider\SocialLinksDataProvider
```

```php
<?php

declare(strict_types=1);

namespace Vendor\MyExtension\DataProvider;

use Smic\Webcomponents\DataProvider\AbstractDataProvider; // implements Smic\Webcomponents\DataProvider\DataProviderInterface
use TYPO3\CMS\Core\Resource\AbstractFile;
use TYPO3\CMS\Core\Resource\FileReference;

class SocialLinksDataProvider extends AbstractDataProvider
{
    public function getProperties(): ?array
    {
        $twitterUrl = $this->getTwitterUrl();
        $instagramUrl = null; // to demonstrate that property is not set
        
        if(empty($twitterUrl) && empty($instagramUrl)) {
            return null; // web component is not rendered if both urls are not available
        }
        
        return [
            'twitterUrl' => $twitterUrl,
            'instagramUrl' => $instagramUrl,
        ];
    }

    public function getTagName(): ?string
    {
        return 'my-sociallinks';
    }
}
```

Output: `<my-sociallinks twitterUrl="https://www.twitter.com/mycompany"></my-sociallinks>`

## TypoScript Reference

This extension introduced the `WEBCOMPONTENT` content object. It has the following properties

| property name  | data type                 | description                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
|----------------|---------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `dataProvider` | `string`                  | Reference to PHP class that can provide properties, tag content and a tag name. It has to implement `\Smic\Webcomponents\DataProvider\DataProviderInterface`.                                                                                                                                                                                                                                                                                                   |
| `properties`   | `array of string/stdWrap` | Each key inside `properties` corresponds to a web component property. If used in conjunction with a `dataProvider` these properties are added to the ones of the dataProvider or override properties that had been previously set by the dataProvider. Properties set via TypoScript are always included in the output, also then they are empty (while in the dataProvider you have the option to set a property to `null` which excludes it from the output). | 
| `stdWrap`      | `stdWrap`                 | Executes stdWrap functions on the rendered tag.                                                                                                                                                                                                                                                                                                                                                                                                                 |
| `tagName`      | `string/stdWrap`          | Sets the tag name. If used in conjunction with a `dataProvider` this settings takes precedence. If the tag name ends up being empty, the web component is not rendered.                                                                                                                                                                                                                                                                                         |

