<?php

namespace App\Controller;


use App\Service\ApiService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class HomeController extends AbstractController
{
    public function index(Request $request)
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

        $dimensions = [];
        foreach ($items as $item) {
            $dimensions[$item->id] = $this->parseDimensions($item->offer->displayName);
        }

        foreach ($dimensions as $dimension) {
            if ($dimension) {
                $response = $apiService->setDimensions($order, $dimension);
            }
        }
        
        return new Response($response->success);
    }

    private function parseDimensions($productName)
    {
        $res = explode('(', $productName);
        $res = end($res);
        $dimensions = explode('x', $res);
        if (count($dimensions) < 3) {
            return false;
        }

        foreach ($dimensions as $key => $dimension) {
            $dimensions[$key] = (int) $dimension;
        }

        return $dimensions;
    }
}
