<?php namespace Bedard\Shop\Traits;

use Omnipay\Common\CreditCard;

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
        $items = [];
        foreach ($this->cart->items as $item) {
            $items[] = [
                'name'          => $item->name,
                'description'   => 'foo', // todo:
                'quantity'      => $item->quantity,
                'price'         => $item->price,
            ];
        }

        // // Add a promotion code with a negative value
        // if ($this->payment->promotion_id) {
        //     $items[] = [
        //         'name'          => 'Promotion code "'.$this->payment->promotion_code.'"',
        //         'quantity'      => 1,
        //         'price'         => $this->payment->promotion_value * -1,
        //     ];
        // }

        return $items;
    }

}
