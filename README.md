# My-CoolPay PHP SDK

[![Latest Version on Packagist][ico-version]][packagist-mycoolpay-php-sdk]
[![Total Downloads][ico-downloads]][packagist-mycoolpay-php-sdk]
[![PHP version][ico-php-version]][packagist-mycoolpay-php-sdk]  
My-CoolPay official SDK for PHP

## Note

This PHP package let you easily integrate [**My-CoolPay Payment API**][mycoolpay] to your application or your
website.
Before you start using this package, it's highly recommended to check resources below depending on your use case.

- [My-CoolPay API Docs][api-docs]
- [My-CoolPay WordPress Docs][wordpress-docs]

## Table of contents

* [1. Requirements](#1-requirements)
* [2. Installation](#2-installation)

## 1. Requirements

This package requires: ![Required PHP version][ico-php-version]

## 2. Installation

### 2.1. Install Composer package

First of all, install Composer package from your CMD or your Terminal.

```bash
$ composer require mycoolpay/php-sdk
```

### 2.2. Initialize SDK object

Now you need to initialize SDK object with your API keys.

```php
<?php

require_once "vendor/autoload.php";

use MyCoolPay\Logging\Logger;
use MyCoolPay\MyCoolPayClient;

define("MCP_PUBLIC_KEY", "<Add your public key here>"); // Your API public key
define("MCP_PRIVATE_KEY", "<Add your private key here>"); // Your API private key

$logger = new Logger('app.log', __DIR__);
$mycoolpay = new MyCoolPayClient(MCP_PUBLIC_KEY, MCP_PRIVATE_KEY, $logger, true);
```

#### Recommendations:

- It is better for you to save your keys outside the source code, in places like environment variables and just load
  them from source code
- Logger object is optional, it is used for debug purposes. To know more about My-CoolPay Logger class
  see [My-CoolPay PHP Logger][repo-php-logger]
- Both Logger and MyCoolPayClient objects don't need to be instantiated every time you need them, just instantiate them
  once (either at application startup or at first use) then share the instances within whole app with patterns such as
  Singleton or Dependency Injection

#### Logger constructor parameters:

See [My-CoolPay PHP Logger][repo-php-logger]

#### MyCoolPayClient constructor parameters:

```php
MyCoolPayClient($public_key, $private_key, $logger = null, $debug = false)
```

| Parameter      | Type                                        | Default | Description             |
|----------------|---------------------------------------------|---------|-------------------------|
| `$public_key`  | `string`                                    |         | Your API public key     |
| `$private_key` | `string`                                    |         | Your API private key    |
| `$logger`      | [`?LoggerInterface`][file-logger-interface] | `null`  | Logger object           |
| `$debug`       | `boolean`                                   | `false` | true enables debug mode |

## 3. Interact with API

### 3.1. Paylink

```php
<?php

use Exception;

try {
    $response = $mycoolpay->paylink([
        "transaction_amount" => 100,
        "transaction_currency" => "XAF",
        "transaction_reason" => "Bic pen",
        "app_transaction_ref" => "order_123",
        "customer_phone_number" => "699009900",
        "customer_name" => "Bob MARLEY",
        "customer_email" => "bob@mail.com",
        "customer_lang" => "en",
    ]);
    
    $transaction_ref = $response->get("transaction_ref"); // You can store this reference to order in your database
    
    $payment_url = $response->get("payment_url"); // Redirect customer to this url

} catch (Exception $exception) {
    $logger->logException($exception);
}
```

#### Method description:

```php
paylink(array $data): MyCoolPay\Http\Response
```

- @param `array` `$data`
- @return [`MyCoolPay\Http\Response`][file-response-class]
- @throws [`MyCoolPay\Http\Exception\HttpException`][file-http-exception]

#### Data description:

For full `$data` description check [Paylink section in API Docs][api-docs-paylink]

### 3.2. Payin

```php
<?php

use Exception;

try {
    $response = $mycoolpay->payin([
        "transaction_amount" => 100,
        "transaction_currency" => "XAF",
        "transaction_reason" => "Bic pen",
        "app_transaction_ref" => "order_123",
        "customer_phone_number" => "699009900",
        "customer_name" => "Bob MARLEY",
        "customer_email" => "bob@mail.com",
        "customer_lang" => "en",
    ]);
    
    $transaction_ref = $response->get("transaction_ref"); // You can store this reference to order in your database
    
    $action = $response->get("action"); // This tells you what to do next
    
    if ($action === "REQUIRE_OTP") {
        // Ask your user to provide OTP received by SMS
        // Then perform OTP request (see next section)
    } elseif ($action === "PENDING") {
        $ussd = $response->get("ussd"); // Tell user to dial this USSD code on his phone
    } else {
        throw new Exception("Unknown action '$action' in Payin response");
    }

} catch (Exception $exception) {
    $logger->logException($exception);
}
```

#### Method description:

```php
payin(array $data): MyCoolPay\Http\Response
```

- @param `array` `$data`
- @return [`MyCoolPay\Http\Response`][file-response-class]
- @throws [`MyCoolPay\Http\Exception\HttpException`][file-http-exception]

#### Data description:

For full `$data` description check [Payin section in API Docs][api-docs-payin]

### 3.3. OTP

```php
<?php

use Exception;

try {
    $response = $mycoolpay->authorizePayin([
        "transaction_ref" => "f4fd89ea-e647-462c-9489-afc0aeb90d5f",
        "code" => "123456",
    ]);
    
    $action = $response->get("action"); // This tells you what to do next
    
    if ($action === "PENDING") {
        $ussd = $response->get("ussd"); // Tell user to dial this USSD code on his phone
    } else {
        throw new Exception("Unknown action '$action' in OTP response");
    }

} catch (Exception $exception) {
    $logger->logException($exception);
}
```

#### Method description:

```php
authorizePayin(array $data): MyCoolPay\Http\Response
```

- @param `array` `$data`
- @return [`MyCoolPay\Http\Response`][file-response-class]
- @throws [`MyCoolPay\Http\Exception\HttpException`][file-http-exception]

#### Data description:

For full `$data` description check [OTP section in API Docs][api-docs-otp]

### 3.4. Payout

```php
<?php

use Exception;
use MyCoolPay\Http\Http;

try {
    $response = $mycoolpay->payout([
        "transaction_amount" => 500,
        "transaction_currency" => "XAF",
        "transaction_reason" => "Customer refund",
        "transaction_operator" => "CM_OM",
        "app_transaction_ref" => "refund_123",
        "customer_phone_number" => "699009900",
        "customer_name" => "Bob MARLEY",
        "customer_email" => "bob@mail.com",
        "customer_lang" => "en",
    ]);
    
    $transaction_ref = $response->get("transaction_ref"); // You can store this reference to payout in your database
    
    $status_code = $response->getStatusCode(); // HTTP status code
    $message = $response->getMessage();
    
    if ($status_code === Http::OK) {
        // Successful withdrawal
    } elseif ($status_code === Http::ACCEPTED) {
        // Withdrawal is pending in background
    } else {
        throw new Exception($message, $status_code);
    }

} catch (Exception $exception) {
    $logger->logException($exception);
}
```

#### Method description:

```php
payout(array $data): MyCoolPay\Http\Response
```

- @param `array` `$data`
- @return [`MyCoolPay\Http\Response`][file-response-class]
- @throws [`MyCoolPay\Http\Exception\HttpException`][file-http-exception]

#### Data description:

For full `$data` description check [Payout section in API Docs][api-docs-payout]

### 3.5. Check status

```php
<?php

use Exception;

try {
    $response = $mycoolpay->checkStatus("f4fd89ea-e647-462c-9489-afc0aeb90d5f");
    
    $status = $response->get("transaction_status");
    
    // Do whatever you need with $status
    // Possible values are PENDING, SUCCESS, CANCELED and FAILED

} catch (Exception $exception) {
    $logger->logException($exception);
}
```

#### Method description:

```php
checkStatus(string $transaction_ref): MyCoolPay\Http\Response
```

- @param `string` `$transaction_ref`
- @return [`MyCoolPay\Http\Response`][file-response-class]
- @throws [`MyCoolPay\Http\Exception\HttpException`][file-http-exception]

#### More information:

For more information check [Status section in API Docs][api-docs-status]

### 3.6. Get balance

```php
<?php

use Exception;

try {
    $response = $mycoolpay->getBalance();
    
    $balance = $response->get("balance");
    
    // Do whatever you need with $balance

} catch (Exception $exception) {
    $logger->logException($exception);
}
```

#### Method description:

```php
getBalance(): MyCoolPay\Http\Response
```

- @return [`MyCoolPay\Http\Response`][file-response-class]
- @throws [`MyCoolPay\Http\Exception\HttpException`][file-http-exception]

#### More information:

For more information check [Balance section in API Docs][api-docs-balance]

## 3. Security helpers

### 3.1. IP verification

```php
<?php

use Exception;

try {
    // Adapt this to the framework you are using to get requester IP address
    $remote_ip = $_SERVER['REMOTE_ADDR'];
    
    if ($mycoolpay->isVerifiedIp($remote_ip)) {    
        // Process callback
    }

} catch (Exception $exception) {
    $logger->logException($exception);
}
```

#### Method description:

```php
isVerifiedIp(string $ip): true
```

- @param `string` `$ip`
- @return `true`
- @throws [`MyCoolPay\Exception\UnknownIpException`][file-ip-exception]

#### More information:

For more information check [Security section in API Docs][api-docs-security]

### 3.2. Callback request integrity

```php
<?php

use Exception;

try {
    // Adapt this to the framework you are using to get REST JSON data
    $callback_data = json_decode(file_get_contents('php://input'), true);
    
    if ($mycoolpay->checkCallbackIntegrity($callback_data)) {    
        // Process callback
    }

} catch (Exception $exception) {
    $logger->logException($exception);
}
```

#### Method description:

```php
checkCallbackIntegrity(string $callback_data): true
```

- @param `array` `$callback_data`
- @return `true`
- @throws [`MyCoolPay\Exception\KeyMismatchException`][file-key-exception]
- @throws [`MyCoolPay\Exception\BadSignatureException`][file-signature-exception]

#### More information:

For more information check [Security section in API Docs][api-docs-security]

## More assistance

ℹ️ If you need further information or assistance, our teams are available at [support@my-coolpay.com][mailto-support].
You can also get help from the [developer community 💬][forum-telegram]

## Contributors

- [Digital House International][contributor-dhi]
- [Willy KOUOGANG][contributor-wkouo]
- [Samuel BAKON][contributor-sbak]
- [Laetitia NGOMTCHO][contributor-lngom]
- [Arnold NGASSAM][contributor-angass]
- [Aristide Herve MBASSI][contributor-ambass]
- [Aubry Yvan FANDOM][contributor-yfan]

[ico-version]: https://img.shields.io/packagist/v/mycoolpay/php-sdk

[ico-downloads]: https://img.shields.io/packagist/dt/mycoolpay/php-sdk

[ico-php-version]: https://img.shields.io/packagist/php-v/mycoolpay/php-sdk

[mycoolpay]: https://my-coolpay.com

[github-mycoolpay]: https://github.com/mycoolpay

[packagist-mycoolpay-php-sdk]: https://packagist.org/packages/mycoolpay/php-sdk

[api-docs]: https://documenter.getpostman.com/view/17178321/UV5ZCx8f

[api-docs-paylink]: https://documenter.getpostman.com/view/17178321/UV5ZCx8f#e610fa62-3e27-400f-b012-a60091201ef0

[api-docs-payin]: https://documenter.getpostman.com/view/17178321/UV5ZCx8f#66c6881a-1cb9-4532-b47a-c1d09a3f2741

[api-docs-otp]: https://documenter.getpostman.com/view/17178321/UV5ZCx8f#f4fd89ea-e647-462c-9489-afc0aeb90d5f

[api-docs-payout]: https://documenter.getpostman.com/view/17178321/UV5ZCx8f#5c202ba5-c602-4106-9347-c055ba8fdf17

[api-docs-status]: https://documenter.getpostman.com/view/17178321/UV5ZCx8f#4c0bcbae-66a2-4312-8124-feb6d986d296

[api-docs-balance]: https://documenter.getpostman.com/view/17178321/UV5ZCx8f#95bae9d4-30a6-413b-9f27-b15b7dfe12b1

[api-docs-security]: https://documenter.getpostman.com/view/17178321/UV5ZCx8f#0c4cf5ed-f0c6-4dea-bd2e-0b4a3dd03687

[wordpress-docs]: https://documenter.getpostman.com/view/17178321/UV5aeFBY

[repo-php-logger]: https://github.com/mycoolpay/php-logger

[file-logger-interface]: https://github.com/mycoolpay/php-logger/blob/master/src/LoggerInterface.php

[file-response-class]: https://github.com/mycoolpay/php-rest-client/blob/master/src/Response.php

[file-http-exception]: https://github.com/mycoolpay/php-rest-client/blob/master/src/Exception/HttpException.php

[file-ip-exception]: https://github.com/mycoolpay/php-sdk/blob/master/src/Exception/UnknownIpException.php

[file-key-exception]: https://github.com/mycoolpay/php-sdk/blob/master/src/Exception/KeyMismatchException.php

[file-signature-exception]: https://github.com/mycoolpay/php-sdk/blob/master/src/Exception/BadSignatureException.php

[contributor-dhi]: https://www.digitalhouse-int.com/

[contributor-wkouo]: https://www.linkedin.com/in/willykouogang/

[contributor-sbak]: https://www.linkedin.com/in/samuel-bakon-gl/

[contributor-lngom]: https://www.linkedin.com/in/laetitia-ngomtcho-b1a312149/

[contributor-angass]: https://www.linkedin.com/in/arnold-ngassam-777022153/

[contributor-ambass]: https://www.linkedin.com/in/aristide-herve-mbassi-92197814a/

[contributor-yfan]: https://www.linkedin.com/in/aubry-yvan-fandom-82aa41108/

[mailto-support]: mailto:support@my-coolpay.com

[forum-telegram]: https://t.me/mycoolpay
