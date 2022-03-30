<?php

namespace App\Controller;


use App\Service\ApiService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class HomeController extends AbstractController
{
    public function index(Request $request, LoggerInterface $logger)
    {
        $id = $request->get('id');

        if (!$id) {

            return new Response('Id is missing');
        }

        $apiUrl = $this->getParameter('app.api_url');
        $apiKey = $this->getParameter('app.api_key');
        $apiService = new ApiService($apiUrl, $apiKey);
        $order = $apiService->getOrderById($id);

        if ($order == null) {

            return new Response('Order is null');
        }

        $items = $order->items;

        $itemDimensions = [];
        foreach ($items as $item) {
            $itemDimensions[$item->id] = $this->parseDimensions($item->offer->displayName, $logger);
        }

        $itemDimensions = array_filter($itemDimensions, function ($item) {
            return $item;
        });

        $lastItemDimensions = end($itemDimensions);

        foreach ($lastItemDimensions as $dimension) {
            if ($dimension) {
                $response = $apiService->setDimensions($order, $dimension);
            }
        }

        if (isset($response->success)) {
            return new Response($response->success);
        }

        return new Response('No dimensions');
    }

    private function parseDimensions($productName, LoggerInterface $logger)
    {
        $res = explode('(', $productName);
        $prodName = array_shift($res);
        $dimensionsString = array_shift($res);

        $dimensions = explode('x', $dimensionsString);


        if (count($dimensions) < 3) {
            $logger->log('error', 'Dimensions not parsed in product name' . $productName, $dimensions);
            return false;
        }

        foreach ($dimensions as $key => $dimension) {
            $dimensions[$key] = (int) $dimension;
        }

        return $dimensions;
    }
}
