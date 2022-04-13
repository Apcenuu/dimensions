<?php

namespace App\Service;

use RetailCrm\Api\Client;
use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Model\Entity\Customers\Customer;
use RetailCrm\Api\Model\Entity\Orders\Order;
use RetailCrm\Api\Model\Filter\Orders\OrderFilter;
use RetailCrm\Api\Model\Request\Customers\CustomersEditRequest;
use RetailCrm\Api\Model\Request\Orders\OrdersEditRequest;
use RetailCrm\Api\Model\Request\Orders\OrdersRequest;

class ApiService
{
    /** @var Client $client */
    private $client;

    public function __construct($apiUrl, $apiKey)
    {
        $this->client = SimpleClientFactory::createClient($apiUrl, $apiKey);
    }

    public function getOrderById($id)
    {
        $request = new OrdersRequest();
        $filter = new OrderFilter();
        $filter->ids = [
            $id
        ];
        $request->filter = $filter;
        $order = $this->client->orders->list($request);

        return array_shift($order->orders);
    }

    public function customerEdit(Customer $customer)
    {
        $request = new CustomersEditRequest();
        $request->by = 'id';
        $request->site = $customer->site;
        $request->customer = $customer;
        $response = $this->client->customers->edit($customer->id, $request);
        return $response;
    }

    public function orderEdit(Order $order)
    {
        $request = new OrdersEditRequest();
        $request->by = 'id';
        $request->site = $order->site;
        $request->order = $order;
        $response = $this->client->orders->edit($order->id, $request);
        return $response;
    }
}
