<?php namespace Bedard\Shop\Traits;

use Omnipay\Common\CreditCard;
use Omnipay\Common\Item;
use Omnipay\Common\ItemBag;

trait OmnipayGatewayTrait {

    /**
     * Returns a CreditCard instance of the current cart
     *
     * @return  CredictCard
     */
    protected function getCard()
    {
        $card = new CreditCard;

        // Set contact details
        $card->setFirstName($this->cart->customer->first_name);
        $card->setLastName($this->cart->customer->last_name);
        $card->setEmail($this->cart->customer->email);

        // Set billing and shipping addresses
        $card->setBillingAddress1($this->cart->billing_address->street_1);
        $card->setBillingAddress2($this->cart->billing_address->street_2);
        $card->setBillingCity($this->cart->billing_address->city);
        $card->setBillingPostcode($this->cart->billing_address->postal_code);
        $card->setBillingState($this->cart->billing_address->state->code);
        $card->setBillingCountry($this->cart->billing_address->country->code);

        $card->setShippingAddress1($this->cart->shipping_address->street_1);
        $card->setShippingAddress2($this->cart->shipping_address->street_2);
        $card->setShippingCity($this->cart->shipping_address->city);
        $card->setShippingPostcode($this->cart->shipping_address->postal_code);
        $card->setShippingState($this->cart->shipping_address->state->code);
        $card->setShippingCountry($this->cart->shipping_address->country->code);

        return $card;
    }

    /**
     * Returns the array of items for an external shopping cart
     *
     * @return  array
     */
    protected function getItems()
    {
        $bag = new ItemBag;

        // Add the CartItem models to the ItemBag
        foreach ($this->cart->items as $item) {
            $add = new Item([
                'name'      => $item->name,
                'price'     => $item->price,
                'quantity'  => $item->quantity,
            ]);

            if ($options = $item->inventory->getValueNames()) {
                $add->setDescription(implode(', ', $options));
            }

            $bag->add($add);
        }

        // Add the promotion if there is one
        if ($this->cart->isUsingPromotion) {
            $bag->add(new Item([
                'name'          => $this->cart->promotion->code,
                'description'   => $this->cart->promotion->message,
                'quantity'      => 1,
                'price'         => $this->cart->promotionSavings * -1,
            ]));
        }

        return $bag;
    }

}
