<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Gateway;

use App\Services\Gateway\Bepusdt;
use GuzzleHttp\Psr7\HttpFactory;
use Illuminate\Database\Capsule\Manager;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Slim\Http\Factory\DecoratedResponseFactory;
use Slim\Http\Factory\DecoratedServerRequestFactory;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

/**
 * BEpusdt Gateway Unit Tests
 *
 * 覆盖：签名构建、签名验证、回调 notify 逻辑
 * 不依赖真实 HTTP / 数据库（使用 SQLite in-memory）
 */
final class BepusdtTest extends TestCase
{
    private Manager $database;

    protected function setUp(): void
    {
        $this->database = new Manager();
        $this->database->addConnection([
            'driver'   => 'sqlite',
            'database' => ':memory:',
        ]);
        $this->database->setAsGlobal();
        $this->database->bootEloquent();

        // 创建 config 表（Config model 依赖）
        $this->database->schema()->create('config', static function ($table): void {
            $table->increments('id');
            $table->string('item');
            $table->text('value');
            $table->string('type')->default('string');
        });

        // 写入 BEpusdt 配置
        $this->database->table('config')->insert([
            ['item' => 'bepusdt_api_url',    'value' => 'https://pay.example.com', 'type' => 'string'],
            ['item' => 'bepusdt_api_token',  'value' => 'test_token_123',          'type' => 'string'],
            ['item' => 'bepusdt_trade_type',  'value' => '',                        'type' => 'string'],
            ['item' => 'bepusdt_expire_time','value' => '10',                      'type' => 'string'],
            ['item' => 'payment_gateway',    'value' => '["bepusdt"]',             'type' => 'string'],
        ]);
    }

    // ─────────────────────── 签名算法测试 ───────────────────────

    /**
     * buildSignature 是私有方法，用反射调用
     */
    private function callBuildSignature(array $params, string $token): string
    {
        $instance = $this->makeBepusdt();
        $method   = new ReflectionMethod(Bepusdt::class, 'buildSignature');
        $method->setAccessible(true);

        return $method->invoke($instance, $params, $token);
    }

    private function callVerifySignature(array $data, string $token): bool
    {
        $instance = $this->makeBepusdt();
        $method   = new ReflectionMethod(Bepusdt::class, 'verifySignature');
        $method->setAccessible(true);

        return $method->invoke($instance, $data, $token);
    }

    /**
     * 官方文档中的签名算法测试用例验证
     */
    public function testOfficialDocSignatureTestVector(): void
    {
        $params = [
            'order_id'     => '20220201030210321',
            'amount'       => 42,
            'notify_url'   => 'http://example.com/notify',
            'redirect_url' => 'http://example.com/redirect',
        ];
        $token = 'epusdt_password_xasddawqe';

        $sig = $this->callBuildSignature($params, $token);

        self::assertSame('1cd4b52df5587cfb1968b0c0c6e156cd', $sig, '必须与官方文档测试向量完全一致');
    }

    /**
     * 签名生成是确定性的（相同输入 → 相同输出）
     */
    public function testBuildSignatureIsDeterministic(): void
    {
        $params = [
            'order_id'   => 'trade-abc123',
            'amount'     => '100',
            'notify_url' => 'https://example.com/payment/notify/bepusdt',
        ];
        $token = 'secret';

        $sig1 = $this->callBuildSignature($params, $token);
        $sig2 = $this->callBuildSignature($params, $token);

        self::assertSame($sig1, $sig2);
        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $sig1, '签名应为 32 位小写 MD5');
    }

    /**
     * 参数顺序不影响签名结果（按 key 升序排序）
     */
    public function testBuildSignatureIsKeyOrderIndependent(): void
    {
        $token = 'secret';

        $params1 = ['amount' => '50', 'order_id' => 'ORD-1', 'notify_url' => 'https://x.com/cb'];
        $params2 = ['notify_url' => 'https://x.com/cb', 'order_id' => 'ORD-1', 'amount' => '50'];

        self::assertSame(
            $this->callBuildSignature($params1, $token),
            $this->callBuildSignature($params2, $token),
            '参数顺序不同，签名应一致'
        );
    }

    /**
     * 不同的 token 产生不同签名
     */
    public function testBuildSignatureDiffersWithDifferentToken(): void
    {
        $params = ['order_id' => 'ORD-1', 'amount' => '99'];

        self::assertNotSame(
            $this->callBuildSignature($params, 'token_a'),
            $this->callBuildSignature($params, 'token_b'),
        );
    }

    /**
     * 空参数 + token 仍能生成合法 MD5
     */
    public function testBuildSignatureWithEmptyParams(): void
    {
        $sig = $this->callBuildSignature([], 'mytoken');
        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $sig);
    }

    // ─────────────────────── 签名验证测试 ───────────────────────

    /**
     * 自签名后验证应通过
     */
    public function testVerifySignaturePassesWithCorrectSignature(): void
    {
        $token  = 'test_token_123';
        $params = [
            'order_id'     => 'trade-xyz',
            'amount'       => '88.00',
            'trade_status' => '2',
        ];
        $params['signature'] = $this->callBuildSignature($params, $token);

        self::assertTrue($this->callVerifySignature($params, $token));
    }

    /**
     * 签名被篡改时验证应失败
     */
    public function testVerifySignatureFailsWithWrongSignature(): void
    {
        $token  = 'test_token_123';
        $params = [
            'order_id'     => 'trade-xyz',
            'amount'       => '88.00',
            'trade_status' => '2',
            'signature'    => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', // 错误签名
        ];

        self::assertFalse($this->callVerifySignature($params, $token));
    }

    /**
     * 缺少 signature 字段时验证应失败
     */
    public function testVerifySignatureFailsWhenSignatureMissing(): void
    {
        $params = ['order_id' => 'trade-xyz', 'trade_status' => '2'];

        self::assertFalse($this->callVerifySignature($params, 'some_token'));
    }

    /**
     * token 为空时验证应直接失败
     */
    public function testVerifySignatureFailsWithEmptyToken(): void
    {
        $params = ['order_id' => 'ORD-1', 'signature' => 'whatever'];

        self::assertFalse($this->callVerifySignature($params, ''));
    }

    // ─────────────────────── notify 回调测试 ───────────────────────

    /**
     * 回调 payload 非 JSON 时，返回 400
     */
    public function testNotifyRejectsNonJsonBody(): void
    {
        $response = $this->makeBepusdt()->notify(
            $this->makeRequest('not-json-at-all'),
            $this->makeResponse(),
            []
        );

        self::assertSame(400, $response->getStatusCode());
    }

    /**
     * 签名错误时，回调返回 400
     */
    public function testNotifyRejectsInvalidSignature(): void
    {
        $payload = json_encode([
            'order_id'     => 'some-order',
            'trade_status' => 2,
            'amount'       => '100',
            'signature'    => 'badsig',
        ]);

        $response = $this->makeBepusdt()->notify(
            $this->makeRequest($payload),
            $this->makeResponse(),
            []
        );

        self::assertSame(400, $response->getStatusCode());
    }

    /**
     * trade_status !== 2 时，回调返回 ok 但不处理（等待/过期状态）
     */
    public function testNotifySkipsNonSuccessStatus(): void
    {
        $token  = 'test_token_123';
        $params = [
            'order_id'     => 'some-order',
            'trade_status' => 1,  // 等待中
            'amount'       => '100',
        ];
        $params['signature'] = $this->callBuildSignature($params, $token);

        $response = $this->makeBepusdt()->notify(
            $this->makeRequest(json_encode($params)),
            $this->makeResponse(),
            []
        );

        self::assertSame('ok', (string) $response->getBody());
    }

    // ─────────────────────── 静态方法测试 ───────────────────────

    public function testNameReturnsCorrectIdentifier(): void
    {
        self::assertSame('bepusdt', Bepusdt::_name());
    }

    public function testReadableNameContainsUSDT(): void
    {
        self::assertStringContainsStringIgnoringCase('usdt', Bepusdt::_readableName());
    }

    public function testEnableReturnsBooleanWhenGatewayActive(): void
    {
        self::assertIsBool(Bepusdt::_enable());
    }

    // ─────────────────────── 辅助方法 ───────────────────────

    private function makeBepusdt(): Bepusdt
    {
        return (new ReflectionClass(Bepusdt::class))->newInstanceWithoutConstructor();
    }

    private function makeRequest(string $body): ServerRequest
    {
        $factory = new HttpFactory();
        $request = (new DecoratedServerRequestFactory($factory))
            ->createServerRequest('POST', '/payment/notify/bepusdt');

        $request->getBody()->write($body);
        $request->getBody()->rewind();

        return $request;
    }

    private function makeResponse(): Response
    {
        $factory = new HttpFactory();

        return (new DecoratedResponseFactory($factory, $factory))->createResponse();
    }
}
