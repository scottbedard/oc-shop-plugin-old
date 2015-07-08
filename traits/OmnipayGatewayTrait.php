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

        $card->setFirstName($this->cart->customer->first_name);
        $card->setLastName($this->cart->customer->last_name);
        $card->setEmail($this->cart->customer->email);

        $card->setAddress1($this->cart->address->street_1);
        $card->setAddress2($this->cart->address->street_2);
        $card->setCity($this->cart->address->city);
        $card->setPostcode($this->cart->address->postal_code);
        $card->setState($this->cart->address->state->code);
        $card->setCountry($this->cart->address->country->code);

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
