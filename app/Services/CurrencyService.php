<?php

namespace App\Services;

use App\Enums\CurrencyEnum;
use App\Models\Currency;

use App\Models\CurrencyChange;
use App\Models\ExchangeInfo;
use App\Models\Holiday;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class CurrencyService
{

    public static function exchangePrice(CurrencyEnum $from, CurrencyEnum $to, $jarId, $oldBalance): float
    {
        $currentExchange = self::getCurrency();

        $currencyInfo = self::learnCurrency();

        $amount = $currentExchange[$from->name][$to->name];

        CurrencyChange::create([
            'change_by' => auth()->id(),
            'amount' => $amount,
            'new_currency' => $to->value,
            'old_currency' => $from->value,
            'currency_info_id' => $currencyInfo->id,
            'jar_id' => $jarId,
            'old_balance' => $oldBalance,
            'new_balance' => PriceService::convertFloat($oldBalance * $amount)
        ]);

        return $amount;

    }

    public static function exchange(CurrencyEnum $from, CurrencyEnum $to, $amount, $date = null): float
    {
        $currentExchange = self::getCurrency($date);
        return PriceService::convertFloat(($currentExchange[$from->name][$to->name]) * $amount);
    }

    public static function getCurrency($date = null)
    {


        $date = $date ?? now();

        $realDate = $date;


        $myCarbon = Carbon::parse($date);

        if (ExchangeInfo::whereDate('date', $myCarbon)->count()) {
            $exchangeInfo = ExchangeInfo::whereDate('date', $myCarbon)->orderByDesc('created_at')->first();
            return json_decode($exchangeInfo->data, true);
        }


        $carbon = CurrencyService::adjustDate($date);


        $add = ($carbon->year == 2022 ? '1691681721003' : '1691651773356');
        $date1 = $carbon->format('Ym/dmY') . ".xml?_$add";

        $url = $carbon->isToday() ? 'https://www.tcmb.gov.tr/kurlar/today.xml' : "https://www.tcmb.gov.tr/kurlar/$date1";
        $currency = [];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $exec = curl_exec($ch);
        curl_close($ch);


        if (str_contains($exec, 'Page Not Found') or ($exec == false)) {
            $hedefTarih = $carbon->format('Y-m-d');
            $enYakinVeri = DB::table('exchnage_infos')
                ->select('*')
                ->whereRaw("ABS(DATEDIFF(date, '$hedefTarih')) = (SELECT MIN(ABS(DATEDIFF(date, '$hedefTarih'))) FROM exchnage_infos)")
                ->first();

            if ($enYakinVeri) {
                $enYakinVeri = json_decode(json_encode($enYakinVeri), 1);
                return json_decode($enYakinVeri['data']) ?? [];
            }
            return [];

        } else {

            $connect_web = new SimpleXMLElement($exec);

            if (isset($connect_web->Currency[3]->BanknoteBuying) && $connect_web->Currency[3]->BanknoteBuying != null && isset($connect_web->Currency[4]->BanknoteBuying) && $connect_web->Currency[4]->BanknoteBuying != null && isset($connect_web->Currency[0]->BanknoteBuying) && $connect_web->Currency[0]->BanknoteBuying != null) {
                $eur = $connect_web->Currency[3]->BanknoteBuying;
                $eur = preg_replace('/[^ ,.%0-9]/', '', $eur);
                $gbp = $connect_web->Currency[4]->BanknoteBuying;
                $gbp = preg_replace('/[^ ,.%0-9]/', '', $gbp);
                $usd = $connect_web->Currency[0]->BanknoteBuying;
                $usd = preg_replace('/[^ ,.%0-9]/', '', $usd);

                $eur_array = array();
                array_push($eur_array, array('TRY' => ((float)$eur), 'USD' => ($eur / $usd), 'GBP' => ($eur / $gbp)));

                $gbp_array = array();
                array_push($gbp_array, array('TRY' => ((float)$gbp), 'EUR' => ($gbp / $eur), 'USD' => ($gbp / $usd)));

                $usd_array = array();
                array_push($usd_array, array("TRY" => ((float)$usd), 'EUR' => ($usd / $eur), 'GBP' => ($usd / $gbp)));

                $try_array = array();
                array_push($try_array, array("EUR" => ((float)1 / $eur), 'GBP' => ((float)1 / $gbp), 'USD' => ((float)1 / $usd)));


                $currency = [
                    'TRY' => $try_array,
                    'EUR' => $eur_array,
                    'USD' => $usd_array,
                    'GBP' => $gbp_array
                ];

                $data = [];
                foreach ($currency as $key => $item) {

                    if (isset($currency[$key][0])) {
                        foreach ($currency[$key][0] as $twoIndex => $value) {
                            $data[$key][$twoIndex] = $value;
                        }
                    }
                }


                foreach ($data as $key => $item) {
                    $data[$key][$key] = 1;
                }

                ExchangeInfo::create([
                        'date' => Carbon::parse($realDate),
                        'data' => json_encode($data)]
                );

                return $data;

            } else {
                $message = ['currency' => 'Currency values could not be obtained from the Central Bank of the Republic of Turkey. Transactions will be made according to the latest exchange rates in the system.'];
                return $message;
            }
        }
    }

    public static function learnCurrency($date = null): ExchangeInfo|null
    {

        self::getCurrency($date);

        $date = $date ?? now();

        $realDate = $date;


        $myCarbon = Carbon::parse($date);

        if (ExchangeInfo::whereDate('date', $myCarbon)->count()) {
            $exchangeInfo = ExchangeInfo::whereDate('date', $myCarbon)->orderByDesc('created_at')->first();
            return $exchangeInfo;
        }


        $carbon = CurrencyService::adjustDate($date);


        $add = ($carbon->year == 2022 ? '1691681721003' : '1691651773356');
        $date1 = $carbon->format('Ym/dmY') . ".xml?_$add";

        $url = $carbon->isToday() ? 'https://www.tcmb.gov.tr/kurlar/today.xml' : "https://www.tcmb.gov.tr/kurlar/$date1";
        $currency = [];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $exec = curl_exec($ch);
        curl_close($ch);


        if (str_contains($exec, 'Page Not Found') or ($exec == false)) {
            $hedefTarih = $carbon->format('Y-m-d');
            $enYakinVeri = DB::table('exchnage_infos')
                ->select('*')
                ->whereRaw("ABS(DATEDIFF(date, '$hedefTarih')) = (SELECT MIN(ABS(DATEDIFF(date, '$hedefTarih'))) FROM exchnage_infos)")
                ->first();

            if ($enYakinVeri) {
                $enYakinVeri = json_decode(json_encode($enYakinVeri), 1);
                return json_decode($enYakinVeri['data']) ?? [];
            }
            return [];

        } else {

            $connect_web = new SimpleXMLElement($exec);

            if (isset($connect_web->Currency[3]->BanknoteBuying) && $connect_web->Currency[3]->BanknoteBuying != null && isset($connect_web->Currency[4]->BanknoteBuying) && $connect_web->Currency[4]->BanknoteBuying != null && isset($connect_web->Currency[0]->BanknoteBuying) && $connect_web->Currency[0]->BanknoteBuying != null) {
                $eur = $connect_web->Currency[3]->BanknoteBuying;
                $eur = preg_replace('/[^ ,.%0-9]/', '', $eur);
                $gbp = $connect_web->Currency[4]->BanknoteBuying;
                $gbp = preg_replace('/[^ ,.%0-9]/', '', $gbp);
                $usd = $connect_web->Currency[0]->BanknoteBuying;
                $usd = preg_replace('/[^ ,.%0-9]/', '', $usd);

                $eur_array = array();
                array_push($eur_array, array('TRY' => ((float)$eur), 'USD' => ($eur / $usd), 'GBP' => ($eur / $gbp)));

                $gbp_array = array();
                array_push($gbp_array, array('TRY' => ((float)$gbp), 'EUR' => ($gbp / $eur), 'USD' => ($gbp / $usd)));

                $usd_array = array();
                array_push($usd_array, array("TRY" => ((float)$usd), 'EUR' => ($usd / $eur), 'GBP' => ($usd / $gbp)));

                $try_array = array();
                array_push($try_array, array("EUR" => ((float)1 / $eur), 'GBP' => ((float)1 / $gbp), 'USD' => ((float)1 / $usd)));


                $currency = [
                    'TRY' => $try_array,
                    'EUR' => $eur_array,
                    'USD' => $usd_array,
                    'GBP' => $gbp_array
                ];

                $data = [];
                foreach ($currency as $key => $item) {

                    if (isset($currency[$key][0])) {
                        foreach ($currency[$key][0] as $twoIndex => $value) {
                            $data[$key][$twoIndex] = $value;
                        }
                    }
                }


                foreach ($data as $key => $item) {
                    $data[$key][$key] = 1;
                }

                return ExchangeInfo::create([
                        'date' => Carbon::parse($realDate),
                        'data' => json_encode($data)]
                );


            } else {
                return null;
            }
        }
    }


    public static function convertEur($unit, Currency $unitCurrency, $date = null): float|int
    {
        $dailyCurrencyEffect = is_null($date) ? self::getCurrency() : self::getCurrency($date);
        if ($dailyCurrencyEffect instanceof \stdClass) {
            $dailyCurrencyEffect = json_decode(json_encode($dailyCurrencyEffect), 1);
        }

        $currencyInfo = (array)$dailyCurrencyEffect[$unitCurrency->name];

        if (is_string($unit)) {
            $unit = PriceService::convertStrToFloat($unit);
        }

        return PriceService::convertFloat($unit * $currencyInfo[CurrencyEnum::EUR->name]);
    }

    public static function adjustDate($inputDate): Carbon
    {
        $inputDate = Carbon::parse($inputDate);

        while ($inputDate->isWeekend() || Holiday::whereDate('date', $inputDate)->exists()) {
            $inputDate->subDay();
        }

        return $inputDate;
    }

}
