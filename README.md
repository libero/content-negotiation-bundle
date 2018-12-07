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

### Path-based negotiation

You can add negotiation to paths through configuration.

For example, to add a requirement of XML or JSON and English or French to an exact path, and plain text and German elsewhere:

```yaml
content_negotiation:
    formats:
        - path: ^/path/to/my/page$
          priorities: xml|json
        - path: ^/
          priorities: txt
    locales:
        - path: ^/path/to/my/page$
          priorities: en|fr
        - path: ^/
          priorities: de
```

The `formats` and `locales` items are run in order. The first to match will be used.

`priorities` may be empty, allowing for negotiation to be disabled at lower levels. For example, require English everywhere except under `/foo`:

```yaml
content_negotiation:
    formats:
        - path: ^/foo($|/)
          priorities:
        - path: ^/
          priorities: en
```

`optional` may be set to `true` to allow falling back to subsequent matches. For example, to require English everywhere except under `/foo`, where either German or English is allowed:

```yaml
content_negotiation:
    formats:
        - path: ^/foo($|/)
          priorities: de
          optional: true
        - path: ^/
          priorities: en
```

### Route-level negotiation

You can add negotiation at the route level by adding requirements for `_format` and/or `_locale`.

These requirements must be a list of possibilities separated by vertical bars.

For example, to add a requirement of XML or JSON and English or French to a route:

```yaml
my_route:
    path: /path/to/my/page
    controller: App\Controller\PageController
    requirements:
        _format: xml|json
        _locale: en|fr
```

Route-level negotiation takes precedence over path-based.

Getting help
------------

- Report a bug or request a feature on [GitHub](https://github.com/libero/libero/issues/new/choose).
- Ask a question on the [Libero Community Slack](https://libero-community.slack.com/).
- Read the [code of conduct](https://libero.pub/code-of-conduct).
