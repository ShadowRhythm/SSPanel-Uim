<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DetectLog;
use App\Models\DetectRule;
use App\Models\Node;
use App\Utils\Tools;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use function in_array;
use function strtolower;

final class DetectLogController extends BaseController
{
    private static array $details =
        [
            'field' => [
                'id' => '事件ID',
                'user_id' => '用户ID',
                'node_id' => '节点ID',
                'node_name' => '节点名',
                'list_id' => '规则ID',
                'rule_name' => '规则名',
                'datetime' => '时间',
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
                ->fetch('admin/log/detect.tpl')
        );
    }

    public function ajax(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        $length = $request->getParam('length');
        $page = $request->getParam('start') / $length + 1;
        $draw = $request->getParam('draw');

        $detect_log = DetectLog::query();

        $search = $request->getParam('search')['value'];

        if ($search !== '') {
            $detect_log->where('user_id', '=', $search)
                ->orWhere('list_id', '=', $search)
                ->orWhere('node_id', '=', $search);
        }

        $whitelist = ['id', 'user_id', 'node_id', 'list_id', 'datetime'];
        $order = strtolower((string) ($request->getParam('order')[0]['dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';

        if ($request->getParam('order')[0]['column'] !== '0') {
            $column_data = $request->getParam('columns')[$request->getParam('order')[0]['column']]['data'] ?? 'id';
            $order_by = in_array($column_data, $whitelist, true) ? $column_data : 'id';

            $detect_log->orderBy($order_by, $order)->orderBy('id', 'desc');
        } else {
            $detect_log->orderBy('id', $order);
        }

        $filtered = $detect_log->count();
        $total = (new DetectLog())->count();

        $logs = $detect_log->paginate($length, '*', '', $page);

        $nodeIds = $logs->pluck('node_id')->unique()->filter()->values();
        $ruleIds = $logs->pluck('list_id')->unique()->filter()->values();

        $nodes = ! $nodeIds->isEmpty() ? Node::whereIn('id', $nodeIds)->get()->keyBy('id') : collect();
        $rules = ! $ruleIds->isEmpty() ? DetectRule::whereIn('id', $ruleIds)->get()->keyBy('id') : collect();

        foreach ($logs as $log) {
            $node = $nodes->get($log->node_id);
            $rule = $rules->get($log->list_id);
            $log->node_name = $node !== null ? $node->name : '节点不存在';
            $log->rule_name = $rule !== null ? $rule->name : '规则不存在';
            $log->datetime = Tools::toDateTime((int) $log->datetime);
        }

        return $response->withJson([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'logs' => $logs,
        ]);
    }
}
