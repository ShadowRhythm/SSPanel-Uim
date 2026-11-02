<?php

declare(strict_types=1);

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\Config;
use App\Models\Product;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use function json_decode;
use function max;
use function min;
use function round;

final class ProductController extends BaseController
{
    /**
     * @throws Exception
     */
    public function index(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        $tabps = (new Product())->where('status', '1')
            ->where('type', 'tabp')
            ->orderBy('id')
            ->get();

        $bandwidths = (new Product())->where('status', '1')
            ->where('type', 'bandwidth')
            ->orderBy('id')
            ->get();

        $times = (new Product())->where('status', '1')
            ->where('type', 'time')
            ->orderBy('id')
            ->get();

        $enableTabpUpgrade = (bool) Config::obtain('enable_tabp_upgrade');
        $activeTabpInfo = $enableTabpUpgrade ? OrderController::getActiveTabpUpgradeInfo($this->user) : null;
        $hasActiveTabp = $activeTabpInfo !== null;
        $activeTabpOrder = $hasActiveTabp ? $activeTabpInfo['order'] : null;
        $credit = $hasActiveTabp ? (float) $activeTabpInfo['credit_amount'] : 0.0;

        foreach ($tabps as $tabp) {
            $tabp->content = json_decode($tabp->content);
            if ($hasActiveTabp && (float) $tabp->price > (float) $activeTabpOrder->price && $tabp->id !== $activeTabpOrder->product_id) {
                $tabp->can_upgrade = true;
                $tabp->upgrade_credit = min($credit, (float) $tabp->price);
                $tabp->upgrade_price = max(0.0, round((float) $tabp->price - $tabp->upgrade_credit, 2));
            } else {
                $tabp->can_upgrade = false;
            }
        }

        foreach ($bandwidths as $bandwidth) {
            $bandwidth->content = json_decode($bandwidth->content);
        }

        foreach ($times as $time) {
            $time->content = json_decode($time->content);
        }

        return $response->write(
            $this->view()
                ->assign('tabps', $tabps)
                ->assign('bandwidths', $bandwidths)
                ->assign('times', $times)
                ->assign('has_active_tabp', $hasActiveTabp)
                ->assign('active_tabp_order', $activeTabpOrder)
                ->fetch('user/product.tpl')
        );
    }
}
