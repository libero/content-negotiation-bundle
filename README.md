ContentNegotiationBundle
========================

[![Build Status](https://travis-ci.com/libero/content-negotiation-bundle.svg?branch=master)](https://travis-ci.com/libero/content-negotiation-bundle)

This is a [Symfony](https://symfony.com/) bundle that will add [content negotiation](https://tools.ietf.org/html/rfc7231#section-5.3) to your application by integrating the [Negotiation library](https://github.com/willdurand/Negotiation).

Getting started
---------------

Using [Composer](https://getcomposer.org/) you can add the bundle as a dependency:

```
composer require libero/content-negotation-bundle
```

If you're not using [Symfony Flex](https://symfony.com/doc/current/setup/flex.html), you'll need to enable the bundle in your application.

### Route-level negotiation

You can add negotiation at the route level by adding requirements for `_format` and/or `_locale`.

For example, to add an requirement of XML or JSON and English or French to a route:

```yaml
my_route:
    path: /path/to/my/page
    controller: App\Controller\PageController
    requirements:
        _format: xml|json
        _locale: en|fr
```

Getting help
------------

- Report a bug or request a feature on [GitHub](https://github.com/libero/libero/issues/new/choose).
- Ask a question on the [Libero Community Slack](https://libero-community.slack.com/).
- Read the [code of conduct](https://libero.pub/code-of-conduct).
