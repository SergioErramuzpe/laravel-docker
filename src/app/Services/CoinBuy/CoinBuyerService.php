<?php


namespace App\Services\CoinBuy;


use App\DataSource\API\CoinDataSource;
use App\DataSource\API\CoinLoreCoinDataSource;
use App\DataSource\Database\EloquentCoinDataSource;
use App\DataSource\Database\EloquentUserDataSource;
use App\DataSource\Database\EloquentWalletDataSource;
use App\Exceptions\CannotCreateOrUpdateACoinException;
use App\Exceptions\CoinIdNotFoundInWalletException;
use App\Exceptions\WalletNotFoundException;
use Exception;
use function PHPUnit\Framework\throwException;
use Illuminate\Http\Request;

class CoinBuyerService
{
    /**
     * @var EloquentUserDataSource
     */
    private $eloquentCoinBuyerDataSource;
    private EloquentWalletDataSource $eloquentWalletDataSource;
    private CoinDataSource $coinLoreCoinDataSource;


    public function __construct(EloquentCoinDataSource $eloquentCoinBuyerDataSource,EloquentWalletDataSource $eloquentWalletDataSource,CoinDataSource $coinLoreCoinDataSource)
    {
        $this->coinLoreCoinDataSource = $coinLoreCoinDataSource;
        $this->eloquentCoinBuyerDataSource = $eloquentCoinBuyerDataSource;
        $this->eloquentWalletDataSource = $eloquentWalletDataSource;
    }

    /**
     * @throws CannotCreateOrUpdateACoinException
     * @throws WalletNotFoundException
     * @throws Exception
     */
    public function execute($coin_id,$wallet_id,$amount_usd): void
    {
        $this->eloquentWalletDataSource->findWalletById($wallet_id);
        $coinInfo = $this->coinLoreCoinDataSource->findCoinById($coin_id);
        try {
            $coin = $this->eloquentCoinBuyerDataSource->findCoinById($coin_id, $wallet_id);
            $this->eloquentCoinBuyerDataSource->updateCoin($wallet_id, $coin_id, ($coin->amount + ($amount_usd/ $coinInfo["price_usd"])), ($coin->value_usd + $amount_usd));
        } catch (CoinIdNotFoundInWalletException $exception) {
            $params = [$wallet_id, $coin_id, $coinInfo['name'], $coinInfo['symbol'], ($amount_usd / $coinInfo['price_usd']), $amount_usd];
            $this->eloquentCoinBuyerDataSource->insertCoin($params);
        }
    }
}
