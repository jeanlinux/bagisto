<?php

namespace Jeanlinux\Rave\Helpers;


use Exception;
use Prettus\Validator\Exceptions\ValidatorException;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\InvoiceRepository;

class Helper
{


    /**
     * OrderRepository object
     *
     * @var \Webkul\Sales\Repositories\OrderRepository
     */
    protected $orderRepository;

    /**
     * InvoiceRepository object
     *
     * @var \Webkul\Sales\Repositories\InvoiceRepository
     */
    protected $invoiceRepository;

    /**
     * Create a new helper instance.
     *
     * @param \Webkul\Sales\Repositories\OrderRepository $orderRepository
     * @param \Webkul\Sales\Repositories\InvoiceRepository $invoiceRepository
     * @return void
     */
    public function __construct(
        OrderRepository $orderRepository,
        InvoiceRepository $invoiceRepository
    )
    {
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
    }


    /**
     * Process order and create invoice
     *
     * @param $order
     * @return void
     * @throws ValidatorException
     */
    public function processOrder($order)
    {

        $this->orderRepository->update(['status' => 'processing'], $order->id);

        if ($order->canInvoice()) {
            try {
                $invoice = $this->invoiceRepository->create($this->prepareInvoiceData($order));

            } catch (Exception $e) {
                Log:
                debug(['Exception occurred:' => " Message => " . $e->getMessage()]);
            }
        }
    }

    /**
     * Prepares order's invoice data for creation
     *
     * @param $order
     * @return array
     */
    protected function prepareInvoiceData($order)
    {
        $invoiceData = [
            "order_id" => $order->id,
        ];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }
}