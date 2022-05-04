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
    public function index(): Response
    {
        // Instantiate the Analytics object
        // optionally pass TRUE in the constructor if you want to connect using HTTPS
        //$analytics = new Analytics(false);
    //https://www.google-analytics.com/mp/collect?measurement_id=G-HY70DVNZXM&api_secret=EVgnlEc8Sc65By6IyTy80g
        //{"client_id":"3540034146","events":[{"name":"tutorial_begin","params":{"currency":"USD","ratio":30.39}}]}
        // Build the GA hit using the Analytics class methods
        // they should Autocomplete if you use a PHP IDE
        //$analytics
        //    ->setProtocolVersion('1')
        //    ->setTrackingId('G-HY70DVNZXM')
        //    ->setClientId('3540034146')
        //    ->setUserAgentOverride('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36')
        //
        //    ->setIpOverride("202.126.106.175");
        //
        //// When you finish bulding the payload send a hit (such as an pageview or event)
        //$res=$analytics->sendPageview();
        for ($i = 0; $i < 100; $i++) {

        try {
            $ratio_data = json_decode(
                file_get_contents('https://api.privatbank.ua/p24api/pubinfo?exchange&json&coursid=11'),
                true
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

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://www.google-analytics.com/mp/collect?measurement_id=G-HY70DVNZXM&api_secret=EVgnlEc8Sc65By6IyTy80g",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                                                  'client_id'=>'3540034146',
                                                  'events'=>[
                                                      'name'=>'USD_UAH_RATIO',
                                                      'params'=>[
                                                          'buy'=>$buy,
                                                          'sell'=>$sale
                                                      ]
                                                  ]
                                              ]),
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
        }
        $response = new Response();
        $response->setContent($response);
        $response->setStatusCode(200);

        return $response;
    }

}