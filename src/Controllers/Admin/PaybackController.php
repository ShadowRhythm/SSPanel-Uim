<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Payback;
use App\Models\User;
use App\Utils\Tools;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

final class PaybackController extends BaseController
{
    private static array $details =
        [
            'field' => [
                'id' => '事件ID',
                'total' => '原始金额',
                'userid' => '发起用户ID',
                'user_name' => '发起用户名',
                'ref_by' => '获利用户ID',
                'ref_user_name' => '获利用户名',
                'ref_get' => '获利金额',
                'invoice_id' => '账单ID',
                'datetime' => '时间',
            ],
        ];

    /**
     * 后台邀请记录页面
     *
     * @throws Exception
     */
    public function index(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        return $response->write(
            $this->view()
                ->assign('details', self::$details)
                ->fetch('admin/log/payback.tpl')
        );
    }

    /**
     * 后台登录记录页面 AJAX
     */
    public function ajax(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        $paybacks = (new Payback())->orderBy('id', 'desc')->get();

        $userIds = $paybacks->pluck('userid')->merge($paybacks->pluck('ref_by'))->unique()->filter()->values();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        foreach ($paybacks as $payback) {
            $payback->datetime = Tools::toDateTime((int) $payback->datetime);
            $user = $users->get($payback->userid);
            $refUser = $users->get($payback->ref_by);
            $payback->user_name = $user !== null ? $user->user_name : '已注销';
            $payback->ref_user_name = $refUser !== null ? $refUser->user_name : '已注销';
        }

        return $response->withJson([
            'paybacks' => $paybacks,
        ]);
    }
}
