<?php

namespace App\Console;

use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GampConsole extends Command
{
    private array $errors = [];

    protected function configure()
    {
        $this->setName('gamp:test')->setDescription('Gamp test');
    }

    /**
     * @throws \JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        for ($i = 0; $i < 100; $i++) {
            $ratioData = $this->getUsdUahRation();
            $this->saveElastic($ratioData);
            $this->sendGaData($ratioData);
            $output->writeln(json_encode($ratioData, JSON_THROW_ON_ERROR));
        }
        return 0;
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
    private function sendGaData($ratioData):bool{
        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL            => "https://www.google-analytics.com/mp/collect?measurement_id=G-HY70DVNZXM&api_secret=EVgnlEc8Sc65By6IyTy80g",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => "",
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => "POST",
                CURLOPT_POSTFIELDS     => json_encode(
                    [
                        'client_id' => '3540034146',
                        'events'    => [
                            'name'   => 'USD_UAH_RATIO',
                            'params' => $ratioData
                        ]
                    ],
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
                $this->errors[]=(new \Exception($err));
                return false;
            }

            return true;
        }catch (\Exception $e){
            $this->errors[] = $e;
            return false;
        }
    }

    #[ArrayShape(['buy' => "float", 'sale' => "float"])] private function getUsdUahRation(): array
    {
        try {
            $buy = 29 + (random_int(-10, 10) / 100);
            $sale = 31 + (random_int(-10, 10) / 100);
            $ratio_data = json_decode(
                file_get_contents('https://api.privatbank.ua/p24api/pubinfo?exchange&json&coursid=11'),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
            foreach ($ratio_data as $ration_value) {
                if ($ration_value['ccy'] === 'USD' && $ration_value['base_ccy'] === 'UAH') {
                    $buy = $ration_value['buy'] + (random_int(-10, 10) / 100);
                    $sale = $ration_value['sale'] + (random_int(-10, 10) / 100);
                }
            }
        } catch (\Exception $e) {
            $this->errors[] = $e;
        }

        return [
            'buy'  => round($buy, 3),
            'sale' => round($sale, 3)
        ];
    }
}