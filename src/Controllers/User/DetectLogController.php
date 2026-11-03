<?php

declare(strict_types=1);

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\Config;
use App\Models\DetectLog;
use App\Models\DetectRule;
use App\Models\Node;
use App\Services\I18n;
use App\Utils\Tools;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

final class DetectLogController extends BaseController
{
    /**
     * 审计碰撞记录
     *
     * @throws Exception
     */
    public function index(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        if (! Config::obtain('display_detect_log')) {
            return $response->withRedirect('/user');
        }

        $logs = (new DetectLog())->orderBy('id', 'desc')->where('user_id', $this->user->id)->get();

        $nodeIds = $logs->pluck('node_id')->unique()->filter();
        $ruleIds = $logs->pluck('list_id')->unique()->filter();
        $nodes = ! $nodeIds->isEmpty() ? Node::whereIn('id', $nodeIds)->pluck('name', 'id') : collect();
        $rules = ! $ruleIds->isEmpty() ? DetectRule::whereIn('id', $ruleIds)->get()->keyBy('id') : collect();
        $locale = I18n::getCurrentLocale($this->user);

        foreach ($logs as $log) {
            $log->node_name = $nodes[$log->node_id] ?? I18n::trans('user.node_not_found', $locale);
            $rule = $rules[$log->list_id] ?? null;
            if ($rule !== null) {
                $rule->type = $rule->type();
                $log->rule = $rule;
            } else {
                $dummyRule = new \stdClass();
                $dummyRule->name = I18n::trans('user.rule_not_found', $locale);
                $dummyRule->text = I18n::trans('user.rule_deleted', $locale);
                $dummyRule->type = I18n::trans('common.unknown', $locale);
                $log->rule = $dummyRule;
            }
            $log->datetime = Tools::toDateTime((int) $log->datetime);
        }

        return $response->write($this->view()
            ->assign('logs', $logs)
            ->fetch('user/detect/log.tpl'));
    }
}
