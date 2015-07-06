<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Classes\ShippingBase;
use Bedard\Shop\Classes\ShippingInterface;

use Bedard\Shop\Models\ShippingRate;

class ShippingTable extends ShippingBase implements ShippingInterface {

    /**
     * Calculate and save the shipping rates
     */
    public function getRates()
    {
        // Query the available rates for this cart with their method
        $rates = ShippingRate::forCart($this->cart)->with('method')->get();

        // Loop through the rates and calculate the cost
        $results = [];
        foreach ($rates as $rate) {
            $results[] = [
                'rate_id'   => $rate->id,
                'method_id' => $rate->method->id,
                'name'      => $rate->method->name,
                'cost'      => round($rate->base_price + ($rate->rate * $this->cart->weight), 2),
            ];
        }

        // Loop through the costs and remove same-method rates at higher costs
        $final = [];
        foreach ($results as $result) {
            $better = (bool) array_filter($results, function($better) use ($result) {
                return
                    $result['rate_id'] != $better['rate_id'] &&
                    $result['method_id'] == $better['method_id'] &&
                    $result['cost'] >= $better['cost'];
            });

            if (!$better) {
                $final[] = [
                    'class' => self::class,
                    'name'  => $result['name'],
                    'cost'  => $result['cost'],
                ];
            }
        }

        return $final;
    }
}
