<?php

namespace App\Controller;

use FourLabs\GampBundle\FourLabsGampBundle;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

class GampController extends AbstractController
{
    public function __construct()
    {

    }

    /**
     * @throws \JsonException
     */
    public function index(): Response
    {
        try {
            $ratio_data = json_decode(
                file_get_contents('https://api.privatbank.ua/p24api/pubinfo?exchange&json&coursid=11'),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
            foreach ($ratio_data as $ration_value) {
                if ($ration_value['ccy'] === 'USD' && $ration_value['base_ccy'] === 'UAH') {
                    $buy = $ration_value['buy'] + (mt_rand(-10, 10) / 100);
                    $sale = $ration_value['sale'] + (mt_rand(-10, 10) / 100);
                }
            }
        }catch (\Exception $e){
            $buy=29+(mt_rand(-10, 10)/100);
            $sale=31+(mt_rand(-10, 10)/100);
        }


        $curl = curl_init();
            $ratioData=[
                'buy'=>round($buy,3),
                'sell'=>round($sale,3)
            ];
            $this->saveElastic($ratioData);
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://www.google-analytics.com/mp/collect?measurement_id=G-HY70DVNZXM&api_secret=EVgnlEc8Sc65By6IyTy80g",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode(
                [
                    'client_id' => '3540034146',
                    'events'    => [
                        'name' => 'USD_UAH_RATIO',
                        'params' => $ratioData
                    ]
                ],
                JSON_THROW_ON_ERROR
            ),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ],
        ]);


        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo $response;
        }

        $response = new Response();
        $response->setContent(json_encode($ratioData, JSON_THROW_ON_ERROR));
        $response->setStatusCode(200);

        return $response;
    }


    private function saveElastic($ratioData):bool{
        try {
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_PORT           => "9200",
                CURLOPT_URL            => "http://elasticsearch:9200/usd_uah_ratio/_doc",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => "",
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => "POST",
                CURLOPT_POSTFIELDS     => json_encode(
                    array_merge($ratioData, ['@timestamp' => date('Y-m-d H:i:s')]),
                    JSON_THROW_ON_ERROR
                ),
                CURLOPT_HTTPHEADER     => [
                    "Content-Type: application/json"
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                return false;
            } else {
                return true;
            }
        }catch (\Exception $e){
            return false;
        }
    }
}