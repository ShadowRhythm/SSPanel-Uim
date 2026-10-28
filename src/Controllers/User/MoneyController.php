<?php

declare(strict_types=1);

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\GiftCard;
use App\Models\UserMoneyLog;
use App\Services\I18n;
use App\Utils\Tools;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use function time;

final class MoneyController extends BaseController
{
    /**
     * @throws Exception
     */
    public function index(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        $user = $this->user;
        $moneylogs = (new UserMoneyLog())->where('user_id', $user->id)->orderBy('id', 'desc')->get();

        foreach ($moneylogs as $moneylog) {
            $moneylog->create_time = Tools::toDateTime($moneylog->create_time);
            $moneylog->remark = UserMoneyLog::localizeRemark($moneylog->remark);
        }

        $moneylog_count = $moneylogs->count();

        return $response->write(
            $this->view()
                ->assign('moneylogs', $moneylogs)
                ->assign('moneylog_count', $moneylog_count)
                ->fetch('user/money.tpl')
        );
    }

    public function applyGiftCard(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        $giftcard_raw = $this->antiXss->xss_clean($request->getParam('giftcard'));
        $giftcard = (new GiftCard())->where('card', $giftcard_raw)->first();

        if ($giftcard === null || $giftcard->status !== 0) {
            return $response->withJson([
                'ret' => 0,
                'msg' => I18n::trans('user.giftcard_invalid'),
            ]);
        }

        $user = $this->user;

        if ($user->is_shadow_banned) {
            return $response->withJson([
                'ret' => 0,
                'msg' => I18n::trans('user.giftcard_invalid'),
            ]);
        }

        $giftcard->status = 1;
        $giftcard->use_time = time();
        $giftcard->use_user = $user->id;
        $giftcard->save();

        $money_before = $user->money;
        $user->money += $giftcard->balance;
        $user->save();

        (new UserMoneyLog())->add(
            $user->id,
            $money_before,
            (float) $user->money,
            $giftcard->balance,
            '礼品卡充值 ' . $giftcard->card
        );

        return $response->withHeader('HX-Refresh', 'true')->withJson([
            'ret' => 1,
            'msg' => I18n::trans('user.topup_success'),
        ]);
    }
}
