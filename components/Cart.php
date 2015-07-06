<?php namespace Bedard\Shop\Components;

use App;
use Bedard\Shop\Models\CartItem;
use Cms\Classes\ComponentBase;

class Cart extends ComponentBase
{
    use \Bedard\Shop\Traits\CartAccessTrait;

    /**
     * Component details
     *
     * @return  array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'bedard.shop::lang.components.cart.name',
            'description' => 'bedard.shop::lang.components.cart.description',
        ];
    }

    /**
     * Run the component
     */
    public function onRun()
    {
        $this->prepareCart();
    }

    /**
     * Add an item to the cart
     */
    public function onAddToCart()
    {
        $productId  = intval(input('product'));
        $valueIds   = array_map('intval', input('options') ?: []);
        $quantity   = intval(input('quantity')) ?: 1;

        $this->manager->addItem($productId, $valueIds, $quantity);
        $this->prepareCart();
    }

    /**
     * Removes all items from the cart
     */
    public function onClearCart()
    {
        $this->manager->clearItems();
        $this->prepareCart();
    }

    /**
     * Remove items from the cart
     */
    public function onRemoveFromCart()
    {
        $this->manager->removeItems(input('remove'));
        $this->prepareCart();
    }

    /**
     * Remove a promotion from the cart
     */
    public function onRemovePromotion()
    {
        $this->manager->removePromotion();
        $this->prepareCart();
    }

    /**
     * Update the cart items
     */
    public function onUpdateCart()
    {
        $this->manager->updateItems(array_map('intval', input('items') ?: []));

        if ($promotion = input('promotion')) {
            $this->manager->applyPromotion($promotion);
        }

        $this->prepareCart();
    }

}
