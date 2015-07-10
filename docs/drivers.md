# oc-shop-plugin drivers

This plugin is still being developed, everything here is subject to change.

- [Getting Started](#getting-started)
- [Driver Installation](#installation)
- [Configuration](#configuration)
- [Validation](#validation)
- [Payment](#payment)
- [Shipping](#shipping)

<a name="getting-started" href="#getting-started"></a>
### Getting Started
The shop supports two different types of drivers, known as `payment` and `shipping` drivers. These can be used integrate the shop with third party services like payment gateways and postal services. A driver *must* imlement it's type interface, and extend it's type base class. The base class and interface for payment drivers are `Bedard\Shop\Classes\PaymentBase` and `Bedard\Shop\Interfaces\PaymentInterface`. The base class and interface for shipping drivers are `Bedard\Shop\Classes\ShippingBase` and `Bedard\Shop\Interfaces\ShippingInterface`. In the example below, we will start creating a basic payment driver.

```php
use Bedard\Shop\Classes\PaymentBase;
use Bedard\Shop\Interfaces\PaymentInterface;

class SomePaymentGateway extends PaymentBase implements PaymentInterface {

}
```

<a name="installation" href="#installation"></a>
### Driver Installation
All driver information lives in the `Driver` model, so all your plugin has to do to install a new driver is simply provide a seed file. Seeds must provide a `name`, `type`, and `class`. The type should be either `shipping` or `payment`, depending on the driver you're making. Lastly, if your driver requires configuration `is_configurable` should be set to `true`.

```php
use Bedard\Shop\Models\Driver;
use System\Models\File;
use October\Rain\Database\Updates\Seeder;

class SeedPaypalDriver extends Seeder
{
    public function run()
    {
        Driver::firstOrCreate([
            'name'              => 'Paypal Express',
            'type'              => 'payment',
            'class'             => 'Bedard\Shop\Drivers\Payment\PaypalExpress',
            'is_configurable'   => true,
        ]);
    }
}
```


<a name="configuration" href="#configuration"></a>
### Configuration
Drivers may offer a configuration form by returning an array of fields from the `registerFields` and/or `registerTabFields` methods. The array of fields should follow the same structure as [normal October forms](http://octobercms.com/docs/backend/forms). If your driver requires sensetive information, such as API keys or passwords, use the field type `api-password` for extra security. In the example below, we'll add a username and password field to our driver.

```php
public function registerFields()
{
    return [
        'username' => [
            'label' => 'Enter username',
            'span'  => 'left',
        ],
        'password' => [
            'label' => 'Enter password',
            'type'  => 'api-password',
            'span'  => 'right',
        ],
    ];
}
```

A driver's configuration values may be be accessed via the `getConfig()` method.

<a name="validation" href="#validation"></a>
### Validation
Configuration can be validated the same way it would be done in a model by defining the `rules` and `customMessages` properties. In this example, we will make both fields required.

```php
public $rules = [
    'username' => 'required',
    'password' => 'required',
];

public $customMessages = [
    'username.required' => 'We need your username!',
    'password.required' => 'We need your password!',
];
```

<a name="payment" href="#payment"></a>
### Payment
Payment driver docs will be written soon.

<a name="shipping" href="#shipping"></a>
### Shipping
Shipping driver docs will be written soon.
