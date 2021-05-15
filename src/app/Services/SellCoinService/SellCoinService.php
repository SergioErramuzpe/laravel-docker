<?php

namespace App\Services\SellCoinService;

use App\DataSource\Database\EloquentCoinSellerDataSource;
use Exception;

class SellCoinService
{
    private EloquentCoinSellerDataSource $eloquentCoinSellerDataSource;

    public function __construct(EloquentCoinSellerDataSource $eloquentCoinSellerDataSource)
    {
        $this->eloquentCoinSellerDataSource = $eloquentCoinSellerDataSource;
    }

    public function execute(string $coinId, int $walletId, float $amountUSD)
    {
        $coin = $this->eloquentCoinSellerDataSource->findCoinById($coinId, $walletId);
        $previousTotalCoinValueUSD = $coin->amount * $coin->value_usd;
        if($previousTotalCoinValueUSD > $amountUSD) {
            $newTotalCoinValueUSD = $previousTotalCoinValueUSD - $amountUSD;
            $newCoinAmount = $newTotalCoinValueUSD / $coin->value_usd;
            $this->eloquentCoinSellerDataSource->sellCoinOperation($coin, $walletId, $newCoinAmount);
        }
        elseif($previousTotalCoinValueUSD === $amountUSD) {
            $this->eloquentCoinSellerDataSource->deleteCoin($coin->id);
        }
        return $coin;
    }
}
