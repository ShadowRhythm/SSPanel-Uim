<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\I18n;
use Illuminate\Database\Query\Builder;

/**
 * @property int    $id              订单ID
 * @property int    $user_id         提交用户ID
 * @property int    $product_id      商品ID
 * @property string $product_type    商品类型
 * @property string $product_name    商品名称
 * @property string $product_content 商品内容
 * @property string $coupon          订单优惠码
 * @property float  $price           订单金额
 * @property string $status          订单状态
 * @property int    $auto_renew      自动续费状态 0:未开启 1:已开启 2:扣款失败
 * @property int    $create_time     创建时间
 * @property int    $update_time     更新时间
 *
 * @mixin Builder
 */
final class Order extends Model
{
    protected $connection = 'default';
    protected $table = 'order';

    protected $casts = [
        'auto_renew' => 'int',
    ];

    /**
     * 自动续费状态文本
     */
    public function autoRenew(): string
    {
        return match ((int) $this->auto_renew) {
            1 => I18n::trans('user.auto_renew_enabled'),
            2 => I18n::trans('user.auto_renew_failed'),
            default => I18n::trans('user.auto_renew_disabled'),
        };
    }

    /**
     * 订单状态
     */
    public function status(): string
    {
        return match ($this->status) {
            'pending_payment' => I18n::trans('user.order_status_pending_payment'),
            'pending_activation' => I18n::trans('user.order_status_pending_activation'),
            'activated' => I18n::trans('user.order_status_activated'),
            'expired' => I18n::trans('user.order_status_expired'),
            'cancelled' => I18n::trans('user.order_status_cancelled'),
            default => I18n::trans('user.order_status_unknown'),
        };
    }

    /**
     * 订单商品类型
     */
    public function productType(): string
    {
        return match ($this->product_type) {
            'tabp' => I18n::trans('user.product_type_tabp'),
            'time' => I18n::trans('user.product_type_time'),
            'bandwidth' => I18n::trans('user.product_type_bandwidth'),
            'topup' => I18n::trans('user.product_type_topup'),
            default => I18n::trans('user.product_type_other'),
        };
    }
}
