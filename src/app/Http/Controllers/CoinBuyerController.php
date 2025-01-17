<?php

namespace App\Http\Controllers;

use App\Errors\Errors;
use App\Services\CoinBuy\CoinBuyerService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class CoinBuyerController extends BaseController {

    private CoinBuyerService $coinBuyerService;

    public function __construct(CoinBuyerService $coinBuyerService)
    {
        $this->coinBuyerService = $coinBuyerService;
    }

    public function buyCoin (Request $request) : JsonResponse
    {
        if (($request->has('coin_id') === false) || ($request->has('wallet_id') === false) || ($request->has('amount_usd') === false)) {
            return response()->json([
                Response::HTTP_BAD_REQUEST => Errors::BAD_REQUEST_ERROR
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->coinBuyerService->execute($request->get('coin_id'),$request->get('wallet_id'),$request->get('amount_usd'));
            return response()->json([
                Response::HTTP_OK => 'successful operation'
            ], Response::HTTP_OK);

        } catch (Exception $exception) {
            return response()->json([
                Response::HTTP_NOT_FOUND => $exception->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

}
