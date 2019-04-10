<?php
require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();
$urlCrm = getenv('URL_CRM');
$apiKey = getenv('API_KEY');

$client = new \RetailCrm\ApiClient(
    $urlCrm,
    $apiKey,
    \RetailCrm\ApiClient::V5
);

try {
    $response = $client->request->customersList(['dateFrom' => '2016-01-01', 'dateTo' => '2017-12-31'], null, 20);
} catch (\RetailCrm\Exception\CurlException $e) {
    echo "Connection error: " . $e->getMessage();
}

if ($response->isSuccessful()) {
    $totalPageCount = $response->pagination['totalPageCount'];
    //$consolidatedClient = $response->customers[0];
    $clientList = [];
    for ($page = 1; $page <= $totalPageCount; $page++) {
        $responseCustomersList = $client->request->customersList(['dateFrom' => '2016-01-01', 'dateTo' => '2017-12-31'], $page, 20);
        foreach ($responseCustomersList->customers as $customer) {
            print_r($customer['email']);
            echo PHP_EOL;
            $emailList[$customer['id']] = $customer['email']; // Получаем список клиентов и их E-mail [id]=>[email]
            $customerList[$customer['id']] = $customer;
        }
    }
    var_dump(count($emailList));

    for ($page = 1; $page <= $totalPageCount; $page++) {
        $responseCustomersList2 = $client->request->customersList(['dateFrom' => '2016-01-01', 'dateTo' => '2017-12-31'], $page, 20);
        foreach ($emailList as $key => $email) {
            foreach ($responseCustomersList2->customers as $customer) {
                // тут добавить условие, чтобы не объединять самого с собой
                if ($email == $customer['email']) {
                    $responseСustomersCombine = $client->request->customersCombine([$customer], $customerList[$key]);
                    var_dump($responseСustomersCombine);
                }
            }
        }
    }
} else {
    echo sprintf(
        "Error: [HTTP-code %s] %s",
        $response->getStatusCode(),
        $response->getErrorMsg()
    );
    if (isset($response['errors'])) {
        print_r($response['errors']);
    }
}
