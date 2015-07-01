# oc-shop-plugin

[![](https://travis-ci.org/scottbedard/oc-shop-plugin.svg)](https://travis-ci.org/scottbedard/oc-shop-plugin)
[![](https://img.shields.io/github/license/mashape/apistatus.svg)](http://opensource.org/licenses/MIT)

This is a work in progress, and is not ready for use by anyone.

An example theme can be found [here](https://github.com/scottbedard/oc-shop-theme).

### Categories
Category docs will be written soon.

### Discounts
Discounts are used to temporarily modify the price of a product. A discount may apply to any product, and any "non-filtered" category. To create a discount that is active during a certain date range, simple select a `start date` and/or `end date`. Note that discounts with no start date will begin immediately, and discounts with no end date will run indefinitely. A discount may modify prices by an exact amount, or an amount relative to the original price. To specify this, adjust the discount's `amount` and `method` fields.

By default, the category scope of a discount will include inherited categories. Consider the following category structure...
```
> Clothing
    > Shirts
    > Pants
```
If the Clothing category has product inheritance enabled, then any discount that applies to it will also apply to Shirts and Pants. Product inheritance can be enabled or disabled from the backend Categories page.

### Products
Product docs will be written soon.

### Promotions
Promotions can be thought of as a discount that applies to the user's entire cart. Like discounts, a promotion can effect the checkout cost by an exact or relative amount. To specify this, adjust the promotion's `cart discount` or `shipping discount`, along with the discount `methods`. Also like discounts, a `start date` and `end date` may be specified to limit when a promotion may be used. Promotions with no start date will begin immediately, and promotions with no end date will run indefinitely.

A minimum cart balance may be specified to restrict the promotion to carts over a certain value. To do this, simply define a `cart minimum`. Should a user apply a promotion to a cart under this value, the promotion will still be applied, but will not take effect. Once the user's cart is greater than or equal to the required minimum, the promotion will automatically take effect.

To further limit the use of a promotion, `required products` and `shipping restrictions` may be added. If multiple products are required, the promotion will not take effect until at least one of them is present in the user's cart. If a shipping restriction is present, the promotion's shipping discount will not take effect unless the customer is shipping to one of the specified coutries.
