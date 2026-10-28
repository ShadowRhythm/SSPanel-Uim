<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserMoneyLog;
use App\Services\DataTable;
use App\Utils\Tools;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

final class MoneyLogController extends BaseController
{
    private static array $details =
        [
            'field' => [
                'id' => '事件ID',
                'user_id' => '用户ID',
                'before' => '变动前余额',
                'after' => '变动后余额',
                'amount' => '变动金额',
                'remark' => '备注',
                'create_time' => '变动时间',
            ],
        ];

    /**
     * 后台用户余额记录页面
     *
     * @throws Exception
     */
    public function index(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        return $response->write(
            $this->view()
                ->assign('details', self::$details)
                ->fetch('admin/log/money.tpl')
        );
    }

    /**
     * 后台用户余额记录页面 AJAX
     */
    public function ajax(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        $money_logs = DataTable::paginate(
            (new UserMoneyLog())->newQuery(),
            $request,
            ['id', 'user_id', 'remark'],
            ['id', 'user_id', 'before', 'after', 'amount', 'create_time']
        );

        foreach ($money_logs['data'] as $money_log) {
            $money_log->create_time = Tools::toDateTime((int) $money_log->create_time);
        }

        $money_logs['money_logs'] = $money_logs['data'];
        unset($money_logs['data']);
        return $response->withJson($money_logs);
    }
}
