<?php namespace Bedard\Shop\Console;

use Bedard\Shop\Classes\PaymentProcessor;
use Bedard\Shop\Models\Order;
use Bedard\Shop\Models\PaymentSettings;
use Bedard\Shop\Models\Status;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Lang;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class AbandonedOrders extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'shop:abandoned';

    /**
     * @var string The console command description.
     */
    protected $description = 'Updates payments that have been abandoned.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        $this->description = Lang::get('bedard.shop::lang.console.abandoned_description');

        parent::__construct();
    }

    /**
     * Find abandoned payments and tell their driver to abandon them
     */
    public function fire()
    {
        $abandoned = PaymentSettings::getAbandoned();
        if (!$abandoned) {
            $this->error(Lang::get('bedard.shop::lang.console.abandoned_disabled'));
            return;
        }

        $orders = Order::shouldBeAbandoned($abandoned)
            ->with('cart', 'payment_driver')
            ->get();

        if ($orders && count($orders)) {
            $status = Status::getCore('abandoned');
            foreach ($orders as $order) {
                $driver = $order->payment_driver->getClass();
                $driver->setCart($order->cart);
                $driver->setDriver($order->payment_driver);
                $driver->setOrder($order);
                $driver->abandonPaymentProcess($status);
            }

            $this->info(Lang::get('bedard.shop::lang.console.abandoned_success', ['num' => count($orders)]));
        } else {
            $this->info(Lang::get('bedard.shop::lang.console.abandoned_none'));
        }
    }

}
