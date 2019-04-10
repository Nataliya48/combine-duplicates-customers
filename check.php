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

    $emailList = [];
    for ($page = 1; $page <= $totalPageCount; $page++) {
        $responseCustomersList = $client->request->customersList(['dateFrom' => '2016-01-01', 'dateTo' => '2017-12-31'], $page, 20);
        $emailList = array_map(function ($customer) {
            $emailList['id'] = $customer['id'];
            $emailList['email'] = $customer['email'];
            return $emailList;
        }, $responseCustomersList->customers);
    }
    print_r($emailList);

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
