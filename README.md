# oc-shop-plugin

[![](https://travis-ci.org/scottbedard/oc-shop-plugin.svg)](https://travis-ci.org/scottbedard/oc-shop-plugin)
[![](https://img.shields.io/github/license/mashape/apistatus.svg)](http://opensource.org/licenses/MIT)

This is a work in progress, and is not ready for use by anyone.

An example theme can be found [here](https://github.com/scottbedard/oc-shop-theme).

- [Carts](#carts)
- [Categories](#categories)
- [Customers](#customers)
- [Discounts](#discounts)
- [Payments](#payments)
- [Payment Gateways](#gateways)
- [Products](#products)
- [Promotions](#promotions)
- [Settings & Permissions](#settings)
- [Shipping](#shipping)

<a name="carts" href="#carts"></a>
### Carts
Cart docs will be written soon.

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
Shipping docs will be written soon.
