# Spot-hit API

Spot-hit SMS API PHP client.

## Installation

The recommended way to install Spot-hit SMS API PHP client is through composer:

```bash
$ composer require partikule/spothit-sms-api
```

## Usage

#### Sending simple SMS

```php
<?php

$client = new Spothit\Api\Client('***API_KEY***');

$client->setSmsRecipients(['+336********']);
$client->setSmsSender('AnySender');

$client->send('Yiiii - This is my first SMS');
```

