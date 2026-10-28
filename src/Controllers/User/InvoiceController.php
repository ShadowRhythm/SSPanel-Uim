<?php

declare(strict_types=1);

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\Invoice;
use App\Models\Paylist;
use App\Models\User;
use App\Models\UserMoneyLog;
use App\Services\DB;
use App\Services\DataTable;
use App\Services\Payment;
use App\Utils\Tools;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use function json_decode;
use function json_encode;
use function time;

final class InvoiceController extends BaseController
{
    private static array $details = [
        'field' => [
            'op' => '操作',
            'id' => '账单ID',
            'order_id' => '订单ID',
            'price' => '账单金额',
            'status' => '账单状态',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
            'pay_time' => '支付时间',
        ],
    ];

    /**
     * @throws Exception
     */
    public function index(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        return $response->write(
            $this->view()
                ->assign('details', self::$details)
                ->fetch('user/invoice/index.tpl')
        );
    }

    /**
     * @throws Exception
     */
    public function detail(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        $id = $this->antiXss->xss_clean($args['id']);

        $invoice = (new Invoice())->where('user_id', $this->user->id)->where('id', $id)->first();

        if ($invoice === null) {
            return $response->withRedirect('/user/invoice');
        }

        $paylist = [];

        if ($invoice->status === 'paid_gateway') {
            $paylist = (new Paylist())->where('invoice_id', $invoice->id)->where('status', 1)->first();
        }

        $invoice->status_text = $invoice->status();
        $invoice->create_time = Tools::toDateTime($invoice->create_time);
        $invoice->update_time = Tools::toDateTime($invoice->update_time);
        $invoice->pay_time = Tools::toDateTime($invoice->pay_time);
        $invoice_content = json_decode($invoice->content);

        return $response->write(
            $this->view()
                ->assign('invoice', $invoice)
                ->assign('invoice_content', $invoice_content)
                ->assign('paylist', $paylist)
                ->assign('payments', Payment::getPaymentsEnabled())
                ->fetch('user/invoice/view.tpl')
        );
    }

    public function payBalance(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        $invoice_id = $this->antiXss->xss_clean($request->getParam('invoice_id'));

        return DB::transaction(function () use ($response, $invoice_id): ResponseInterface {
            $invoice = (new Invoice())->where('user_id', $this->user->id)->where('id', $invoice_id)->lockForUpdate()->first();

            if ($invoice === null) {
                return $response->withJson([
                    'ret' => 0,
                    'msg' => '账单不存在',
                ]);
            }

            $user = (new User())->where('id', $this->user->id)->lockForUpdate()->first();

            if ($user === null || $user->is_shadow_banned) {
                return $response->withJson([
                    'ret' => 0,
                    'msg' => '支付失败，请稍后再试',
                ]);
            }

            // 账单是否为充值
            if ($invoice->type === 'topup') {
                return $response->withJson([
                    'ret' => 0,
                    'msg' => '该账单不支持使用余额支付',
                ]);
            }

            if ($invoice->status !== 'unpaid' && $invoice->status !== 'partially_paid') {
                return $response->withHeader('HX-Redirect', '/user/invoice')->withJson([
                    'ret' => 2,
                    'msg' => '已支付成功，无需重复支付',
                ]);
            }

            if ($user->money < $invoice->price) {
                return $response->withJson([
                    'ret' => 0,
                    'msg' => '余额不足',
                ]);
            }

            $money_before = $user->money;
            $user->money -= $invoice->price;
            $user->save();

            (new UserMoneyLog())->add(
                $user->id,
                $money_before,
                (float) $user->money,
                -$invoice->price,
                '支付账单 #' . $invoice->id
            );

            $invoice->status = 'paid_balance';
            $invoice->update_time = time();
            $invoice->pay_time = time();
            $invoice->save();

            return $response->withHeader('HX-Redirect', '/user/invoice');
        });
    }

    public function ajax(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        $invoices = DataTable::paginate(
            (new Invoice())->newQuery()->where('user_id', $this->user->id),
            $request,
            ['id', 'order_id', 'type', 'status'],
            ['id', 'order_id', 'price', 'status', 'create_time', 'update_time', 'pay_time']
        );

        foreach ($invoices['data'] as $invoice) {
            $invoice->op = '<a class="btn btn-primary" href="/user/invoice/' . $invoice->id . '/view">查看</a>';
            $invoice->status = $invoice->status();
            $invoice->create_time = Tools::toDateTime($invoice->create_time);
            $invoice->update_time = Tools::toDateTime($invoice->update_time);
            $invoice->pay_time = Tools::toDateTime($invoice->pay_time);
        }

        $invoices['invoices'] = $invoices['data'];
        unset($invoices['data']);
        return $response->withJson($invoices);
    }
}
