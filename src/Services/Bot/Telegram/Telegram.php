<?php

declare(strict_types=1);

namespace App\Services\Bot\Telegram;

use App\Models\Config;
use App\Models\User;
use App\Services\Cache;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\RequestInterface;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Throwable;
use function trim;

final class Telegram
{
    /**
     * @throws TelegramSDKException|GuzzleException
     */
    public static function process(RequestInterface $request): void
    {
        $bot = new Api(Config::obtain('telegram_token'));

        $bot->addCommands([
            new Commands\MyCommand(),
            new Commands\HelpCommand(),
            new Commands\MenuCommand(),
            new Commands\PingCommand(),
            new Commands\DcCommand(),
            new Commands\StartCommand(),
            new Commands\UnbindCommand(),
            new Commands\CheckinCommand(),
            new Commands\BindCommand(),
        ]);

        $bot->commandsHandler(true, $request);
        $bot->setConnectTimeOut(1);
        $update = $bot->getWebhookUpdate();

        try {
            if ($update->has('callback_query')) {
                new Callback($bot, $update->callbackQuery);
            } elseif ($update->has('message')) {
                new Message($bot, $update->message);
            }
        } catch (Throwable) {
            // ignore to avoid webhook 500 error
        }
    }

    public static function bindUserWithToken(int $telegram_user_id, string $token): string
    {
        $token = trim($token);
        if ($token === '') {
            return '绑定失败：绑定码不能为空。';
        }

        try {
            $redis = (new Cache())->initRedis();
            $user_id = $redis->get('telegram_bind_token:' . $token);
        } catch (Throwable) {
            return '绑定失败：系统缓存服务连接异常。';
        }

        if (! $user_id) {
            return '绑定失败：绑定码不存在或已过期，请在网站重新获取绑定链接。';
        }

        $user = (new User())->find((int) $user_id);
        if ($user === null) {
            return '绑定失败：未找到对应的网站账户。';
        }

        $existing = (new User())->where('im_type', 4)->where('im_value', (string) $telegram_user_id)->first();
        if ($existing !== null) {
            if ((int) $existing->id === (int) $user->id) {
                return "你的 Telegram 账号已经绑定了该账户 ({$user->email})，无需重复绑定。";
            }

            return "绑定失败：此 Telegram 账号已绑定至其他账户 ({$existing->email})。如需换绑，请先解绑原账户。";
        }

        if ($user->im_type === 4 && $user->im_value !== '') {
            return "绑定失败：网站账户 ({$user->email}) 已绑定了其他 Telegram 账号。请在网站个人中心先解绑。";
        }

        $user->im_type = 4;
        $user->im_value = (string) $telegram_user_id;
        $user->save();

        try {
            $redis->del('telegram_bind_token:' . $token);
            $redis->del('telegram_bind_user:' . $user->id);
        } catch (Throwable) {
            // Ignore
        }

        return "🎉 *绑定成功*！\n\n你的 Telegram 账号已成功绑定至网站账户 `{$user->email}`。\n发送 /help 获取指令帮助。";
    }
}
