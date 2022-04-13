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
        $order = $apiService->getOrderById($id);

        if ($order == null) {

            return new Response('Order is null');
        }

        $firstName = $this->removeEmoji($order->customer->firstName);
        $lastName = $this->removeEmoji($order->customer->lastName);

        $order->customer->firstName = $firstName;
        $order->customer->lastName = $lastName;

        $order->phone = '+57' . substr($order->phone, -10);

        foreach ($order->customer->phones as $phone) {
            $cleanPhone = substr($phone->number, -10);
            $phone->number = '+57' . $cleanPhone;
        }

        $orderResponse = $apiService->orderEdit($order);
        $customerResponse = $apiService->customerEdit($order->customer);

        return new JsonResponse([
            'order' => $orderResponse->success ?? false,
            'customer' => $customerResponse->success ?? false
        ]);
    }

    public function removeEmoji(?string $text) {

        $cleanText = "";

        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $cleanText = preg_replace($regexEmoticons, '', $text);

        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $cleanText = preg_replace($regexSymbols, '', $cleanText);

        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $cleanText = preg_replace($regexTransport, '', $cleanText);

        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $cleanText = preg_replace($regexMisc, '', $cleanText);

        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $cleanText = preg_replace($regexDingbats, '', $cleanText);

        return $cleanText;
    }
}
