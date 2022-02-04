<?php

namespace App\Service;

use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Model\Filter\Orders\OrderFilter;
use RetailCrm\Api\Model\Request\Orders\OrdersEditRequest;
use RetailCrm\Api\Model\Request\Orders\OrdersRequest;

class ApiService
{
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

        return $order;
    }

    public function setDimensions($orderContainer, $dimensionsArray)
    {
        $order = array_shift($orderContainer->orders);
        $request = new OrdersEditRequest();
        $request->by = 'id';
        $request->site = $order->site;

        $order->length = $dimensionsArray[0];
        $order->width = $dimensionsArray[1];
        $order->height = $dimensionsArray[2];

        $request->order = $order;
        $response = $this->client->orders->edit($order->id, $request);
        return $response;
    }
}
