<?php

namespace Tests\Services\OpenWalletService;

use App\Infraestructure\Database\DatabaseManager;
use App\Models\Wallet;
use App\Services\OpenWalletService\OpenWalletService;
use Exception;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophet;

class OpenWalletServiceTest extends TestCase
{
    private $databaseManager;
    private OpenWalletService $openWalletService;

    protected function setUp():void
    {
        parent::setUp();
        $prophet = new Prophet;
        $this->databaseManager = $prophet->prophesize(DatabaseManager::class);
        $this->openWalletService = new OpenWalletService($this->databaseManager->reveal());
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function getsErrorWhenAUserDoesNotExist()
    {
        $userId = "invalidUserId";
        $request = Request::create('/wallet/open', 'POST',[
            'userId' => $userId
        ]);

        $this->expectException(Exception::class);

        $this->databaseManager->set("userId", $userId)->willThrow(new Exception("Error"));

        $this->openWalletService->execute($userId);

    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function getsSuccessfulOperationWhenUserIdIsFound ()
    {
        $userId = "validUserId";
        $request = Request::create('/wallet/open', 'POST',[
            'userId' => $userId
        ]);

        $walletId = "validWalletId";
        $wallet = new Wallet($userId, $walletId);
        $this->databaseManager->set("userId", $userId)->willReturn($wallet);

        $response = $this->openWalletService->execute($userId);

        $this->assertEquals($walletId, $response);
    }

}