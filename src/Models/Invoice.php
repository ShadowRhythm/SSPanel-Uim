<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\I18n;
use Illuminate\Database\Query\Builder;
use function in_array;
use function json_decode;
use function json_encode;
use function time;

/**
 * @property int    $id          账单ID
 * @property string $type        账单类型
 * @property int    $user_id     归属用户ID
 * @property string $order_id    订单ID
 * @property string $content     账单内容
 * @property float  $price       账单金额
 * @property string $status      账单状态
 * @property int    $create_time 创建时间
 * @property int    $update_time 更新时间
 * @property int    $pay_time    支付时间
 *
 * @mixin Builder
 */
final class Invoice extends Model
{
    protected $connection = 'default';
    protected $table = 'invoice';

    /**
     * 账单状态
     */
    public function status(): string
    {
        return match ($this->status) {
            'unpaid' => I18n::trans('user.invoice_status_unpaid'),
            'paid_gateway' => I18n::trans('user.invoice_status_paid_gateway'),
            'paid_balance' => I18n::trans('user.invoice_status_paid_balance'),
            'paid_admin' => I18n::trans('user.invoice_status_paid_admin'),
            'cancelled' => I18n::trans('user.invoice_status_cancelled'),
            'refunded_balance' => I18n::trans('user.invoice_status_refunded_balance'),
            'partially_paid' => I18n::trans('user.invoice_status_partially_paid'),
            default => I18n::trans('user.invoice_status_unknown'),
        };
    }

    public function type(): string
    {
        return match ($this->type) {
            'product' => I18n::trans('user.invoice_type_product'),
            'topup' => I18n::trans('user.invoice_type_topup'),
            default => I18n::trans('user.invoice_type_unknown'),
        };
    }

    public function refundToBalance(): void
    {
        if (in_array($this->status, ['paid_gateway', 'paid_balance', 'paid_admin'])) {
            $user = (new User())->find($this->user_id);
            $user->money += $this->price;
            $user->save();

            (new UserMoneyLog())->add(
                $user->id,
                $user->money - $this->price,
                $user->money,
                $this->price,
                '账单 #' . $this->id . ' 退款至账户余额'
            );

            $content = json_decode($this->content, true);
            $content[] = [
                'content_id' => count($content),
                'name' => '退款至账户余额',
                'price' => '-' . $this->price,
            ];

            $this->content = json_encode($content);
            $this->status = 'refunded_balance';
            $this->update_time = time();
            $this->save();
        }
    }
}
