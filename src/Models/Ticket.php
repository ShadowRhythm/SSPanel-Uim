<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\I18n;
use Illuminate\Database\Query\Builder;

/**
 * @property int    $id       工单ID
 * @property string $title    工单标题
 * @property string $content  工单内容
 * @property int    $userid   用户ID
 * @property int    $datetime 创建时间
 * @property string $status   工单状态
 * @property string $type     工单类型
 *
 * @mixin Builder
 */
final class Ticket extends Model
{
    protected $connection = 'default';
    protected $table = 'ticket';

    /**
     * 工单类型
     */
    public function type(): string
    {
        return match ($this->type) {
            'howto' => I18n::trans('user.ticket_type_howto'),
            'billing' => I18n::trans('user.ticket_type_billing'),
            'account' => I18n::trans('user.ticket_type_account'),
            default => I18n::trans('user.ticket_type_other'),
        };
    }

    /**
     * 工单状态
     */
    public function status(): string
    {
        return match ($this->status) {
            'closed' => I18n::trans('user.ticket_status_closed'),
            'open_wait_user' => I18n::trans('user.ticket_status_open_wait_user'),
            'open_wait_admin' => I18n::trans('user.ticket_status_open_wait_admin'),
            default => I18n::trans('user.ticket_status_unknown'),
        };
    }
}
