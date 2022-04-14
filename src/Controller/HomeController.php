<?php

namespace App\Controller;


use App\Service\ApiService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        $customer = $apiService->getCustomerById($id);

        if ($customer == null) {

            return new Response('Customer is null');
        }

        $orders = $apiService->getOrdersByCustomer($customer);
        $response = [];
        foreach ($orders as $order) {
            $order->status = 'procesando-reserva';
            $orderResponse = $apiService->orderEdit($order);
            $response[$order->id] = $orderResponse->success ?? false;
        }

        return new JsonResponse($response);
    }
}
