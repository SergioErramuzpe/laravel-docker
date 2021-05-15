<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\SellCoinController;
use App\Models\Coin;
use App\Services\SellCoinService\SellCoinService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Prophecy\Prophet;
use Tests\TestCase;

class SellCoinControllerTest extends TestCase
{
    use RefreshDatabase;

    private $sellCoinService;
    private SellCoinController $sellCoinController;
    private Prophet $prophet;

    protected function setUp():void
    {
        //parent::setUp();
        $this->prophet = new Prophet;
        $this->sellCoinService = $this->prophet->prophesize(SellCoinService::class);
        $this->sellCoinController = new SellCoinController($this->sellCoinService->reveal());
    }

    /**
     * @test
     * @throws Exception
     */
    public function getsHttpNotFoundWhenInvalidCoinIdIsReceived()
    {
        $coinId = "invalidCoinId";
        $walletId = 1;
        $amountUSD = 0;
        $request = Request::create('/api/coin/sell', 'POST', [
            'coinId' => $coinId,
            'walletId' => $walletId,
            'amountUSD' => $amountUSD
        ]);

        $this->sellCoinService->execute($coinId, $walletId, $amountUSD)
            ->willThrow(new Exception("Error"));

        $response = $this->sellCoinController->sellCoin($request);
        $expectedResponse = response()->json([
            404 => "A coin with specified ID was not found"
        ], Response::HTTP_NOT_FOUND);

        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * @test
     */
    public function coinFoundForGivenId()
    {
        Coin::factory(Coin::class)->create();

        $response = $this->post('/api/coin/sell');

        $response->assertStatus(Response::HTTP_OK)
            ->assertExactJson([200 => "Successful operation"]);
    }

    /**
     * @test
     * @throws Exception
     */
    public function getsHttpBadRequestWhenCoinIdFieldIsNotFound()
    {
        $coinIdField = "invalidCoinIdField";
        $walletId = "validWalletId";
        $amountUSD = 0;
        $request = Request::create('/api/coin/sell', 'POST', [
            $coinIdField => 'coinId',
            'walletId' => $walletId,
            'amountUSD' => $amountUSD
        ]);

        $this->sellCoinService->execute($coinIdField, $walletId, $amountUSD)
            ->willThrow(new Exception("Error"));

        $response = $this->sellCoinController->sellCoin($request);

        $expectedResponse = response()->json([
            400 => "Bad request error"
        ], Response::HTTP_BAD_REQUEST);

        $this->assertEquals($expectedResponse, $response);
    }
}
