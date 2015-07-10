# oc-shop-plugin

[![](https://travis-ci.org/scottbedard/oc-shop-plugin.svg)](https://travis-ci.org/scottbedard/oc-shop-plugin)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/scottbedard/oc-shop-plugin/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/scottbedard/oc-shop-plugin/?branch=master)
[![](https://img.shields.io/github/license/mashape/apistatus.svg)](http://opensource.org/licenses/MIT)

This is a work in progress, and is not ready for use by anyone.

An example theme can be found [here](https://github.com/scottbedard/oc-shop-theme).

- [Carts](#carts)
- [Categories](#categories)
- [Customers](#customers)
- [Discounts](#discounts)
- [Drivers](https://github.com/scottbedard/oc-shop-plugin/blob/master/docs/drivers.md)
- [Payments](#payments)
- [Payment Gateways](#gateways)
- [Products](#products)
- [Promotions](#promotions)
- [Settings & Permissions](#settings)
- [Shipping](#shipping)

<a name="carts" href="#carts"></a>
### Carts
To set up the user's shopping cart, simply attach the `shopCart` component to your layout files. This component provides all handlers related to modifying a user's shopping cart. Please see the demonstration theme for example usage of the component.

The `cart lifetime` can be set from the shop's `general settings` page. When a user leaves your site, their cart will be recoverable until their cart life expires. Once a cart expires, it will be considered abandoned, and this information will be reflected in backend graphs.

The cart comes with optional validation built in. If enabled, it will manage user shopping carts to prevent conflicting items. Consider the following example... Your shop has a shirt that has 1 in stock. Now imagine this shirt is in two different user's shopping carts at the same time. With cart validation enabled, when one of the users completes their order the other's cart will be adjusted to reflect the new inventory. This validation will occur on any page that loads item details from the `shopCart` or `shopCheckout` components. With cart validation disabled, both users will be allowed to check out as long as the product was in stock when it was added to their cart. Please see the demonstration theme for a demonstration of how to trigger an event when a user's cart is modified.

<a name="categories" href="#categories"></a>
### Categories
There are two types of categories to discuss here, first lets cover "normal" categories. A standard category is nothing more than a container for products and other categories. You may nest categories under each other as deeply as want by clicking the `Re-order Categories` button. It is important to note that categories do not have to inherit their children. To change the inheritance behavior, simply set the `Product Inheritance` property.

The second type of categories are called "filtered" categories. These categories do not load products through the normal Category/Product relationship, or through inheritance of a child category. Instead, they use simple logic to query all products and return the appropriate ones. For example, using the `discounted` filter, you can create a dynamic category that will only display products that are currently discounted. Another example might be using the `Created in the last X days` filter with a value of `7`. This would result in a category that only displays products added during the previous week.

Lastly, the appearance of a category can be adjusted using the `Display` tab. From here you can control the pagination, sort order, and product/category visibility.

<a name="customers" href="#customers"></a>
### Customers
Customer docs will be written soon.

<a name="discounts" href="#discounts"></a>
### Discounts
Discounts are used to temporarily modify the price of a product. A discount may apply to any product, and any "non-filtered" category. To create a discount that is active during a certain date range, simple select a `start date` and/or `end date`. Note that discounts with no start date will begin immediately, and discounts with no end date will run indefinitely. A discount may modify prices by an exact amount, or an amount relative to the original price. To specify this, adjust the discount's `amount` and `method` fields.

By default, the category scope of a discount will include inherited categories. Consider the following category structure...
```
> Clothing
    > Shirts
    > Pants
```
If the Clothing category has product inheritance enabled, then any discount that applies to it will also apply to Shirts and Pants. Product inheritance can be enabled or disabled from the backend Categories page.

<a name="payments" href="#payments"></a>
### Payments
Payment docs will be written soon.

<a name="gateways" href="#gateways"></a>
### Payment Gateways
Gateway docs will be written soon.

<a name="products" href="#products"></a>
### Products
Product docs will be written soon.

<a name="promotions" href="#promotions"></a>
### Promotions
Promotions can be thought of as a discount that applies to the user's entire cart. Like discounts, a promotion can effect the checkout cost by an exact or relative amount. To specify this, adjust the promotion's `cart discount` or `shipping discount`, along with the discount `methods`. Also like discounts, a `start date` and `end date` may be specified to limit when a promotion may be used. Promotions with no start date will begin immediately, and promotions with no end date will run indefinitely.

A minimum cart balance may be specified to restrict the promotion to carts over a certain value. To do this, simply define a `cart minimum`. Should a user apply a promotion to a cart under this value, the promotion will still be applied, but will not take effect. Once the user's cart is greater than or equal to the required minimum, the promotion will automatically take effect.

To further limit the use of a promotion, `required products` and `shipping restrictions` may be added. If multiple products are required, the promotion will not take effect until at least one of them is present in the user's cart. If a shipping restriction is present, the promotion's shipping discount will not take effect unless the customer is shipping to one of the specified coutries.

<a name="settings" href="#settings"></a>
### Settings & Permissions
Settings and permission docs will be written soon.

<a name="shipping" href="#shipping"></a>
### Shipping
This plugin provides a basic shipping table out of the box. To get started, you'll first need to create a `shipping method`. Here you can define the `name` of your method, as well as a `minimum weight` and `maximum weight`. Think of shipping methods as a type of shipment. For example, a package between 5lbs and 10lbs. Next, it's time to create some `shipping rates`. Shipping rates define how much you will charge to mail this type of package to a destination. A `base price` and `rate` are used to define the overall cost of the shipping. Lastly, select the `countries` and `states` that the rate should apply to.

If your shop is using an external shipping driver, it is still a good idea to define rates via the shipping table. The reason for this is to prevent a user from receiving a null shipping response should the shipping calculator fail. For example, if your shop is using the [U.S. Postal Service](https://github.com/scottbedard/oc-uspsdriver-plugin) driver, and no response is received, it may pass the request to the shipping table as a backup.
