{include file='user/header.tpl'}

<div class="page-wrapper">
    <div class="container-xl">
        <div class="page-header d-print-none text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <span class="home-title">{trans key='user.order_no' id=$order->id}</span>
                    </h2>
                    <div class="page-pretitle my-3">
                        <span class="home-subtitle">{trans key='user.order_details'}</span>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="btn-list">
                        <a href="/user/invoice/{$invoice->id}/view" targer="_blank" class="btn btn-primary">
                            <i class="icon ti ti-file-dollar"></i>
                            {trans key='user.view_invoice'}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{trans key='user.basic_info'}</h3>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">{trans key='user.product_type'}</div>
                            <div class="datagrid-content">{$order->product_type_text}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">{trans key='user.product_name'}</div>
                            <div class="datagrid-content">{$order->product_name}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">{trans key='user.order_coupon'}</div>
                            <div class="datagrid-content">{$order->coupon}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">{trans key='user.order_amount'}</div>
                            <div class="datagrid-content">{$order->price}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">{trans key='user.order_status'}</div>
                            <div class="datagrid-content">{$order->status}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">{trans key='user.create_time'}</div>
                            <div class="datagrid-content">{$order->create_time}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">{trans key='user.update_time'}</div>
                            <div class="datagrid-content">{$order->update_time}</div>
                        </div>
                        {if $order->product_type === 'tabp' || $order->product_type === 'time'}
                            <div class="datagrid-item">
                                <div class="datagrid-title">自动续费</div>
                                <div class="datagrid-content">
                                    <span id="auto-renew-badge" class="badge {if $order->auto_renew === 1}bg-green text-green-fg{elseif $order->auto_renew === 2}bg-danger text-danger-fg{else}bg-secondary text-secondary-fg{/if} me-2">{$order->auto_renew_text}</span>
                                    {if $order->raw_status === 'activated' || ($order->raw_status === 'expired' && ($order->auto_renew === 1 || $order->auto_renew === 2))}
                                        <button id="toggle-renew-btn" class="btn btn-sm btn-outline-primary ms-1"
                                                hx-post="/user/order/{$order->id}/toggle-renew"
                                                hx-swap="none">
                                            {if $order->auto_renew === 1 || $order->auto_renew === 2}关闭自动续费{else}开启自动续费{/if}
                                        </button>
                                    {/if}
                                </div>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
            {if $order->type === 'topup'}
            <div class="card my-3">
                <div class="card-header">
                    <h3 class="card-title">{trans key='user.product_content'}</h3>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        {if $order->product_type === 'tabp' || $order->product_type === 'time'}
                            <div class="datagrid-item">
                                <div class="datagrid-title">{trans key='user.product_duration_days'}</div>
                                <div class="datagrid-content">{$order->content->time}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">{trans key='user.class_duration_days'}</div>
                                <div class="datagrid-content">{$order->content->class_time}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">{trans key='user.level'}</div>
                                <div class="datagrid-content">{$order->content->class}</div>
                            </div>
                        {/if}
                        {if $order->product_type === 'tabp' || $order->product_type === 'bandwidth'}
                            <div class="datagrid-item">
                                <div class="datagrid-title">{trans key='user.available_traffic_gb'}</div>
                                <div class="datagrid-content">{$order->content->bandwidth}</div>
                            </div>
                        {/if}
                        {if $order->product_type === 'tabp' || $order->product_type === 'time'}
                            <div class="datagrid-item">
                                <div class="datagrid-title">{trans key='user.speed_limit_mbps'}</div>
                                <div class="datagrid-content">
                                    {if $order->content->ip_limit === '0'}
                                        {trans key='user.unlimited'}
                                    {else}
                                        {$order->content->speed_limit}
                                    {/if}
                                </div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">{trans key='user.ip_limit_count'}</div>
                                <div class="datagrid-content">
                                    {if $order->content->ip_limit === '0'}
                                        {trans key='user.unlimited'}
                                    {else}
                                        {$order->content->ip_limit}
                                    {/if}
                                </div>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
            {/if}
            <div class="card my-3">
                <div class="card-header">
                    <h3 class="card-title">{trans key='user.associated_invoice'}</h3>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">{trans key='user.invoice_content'}</div>
                            <div class="datagrid-content">
                                <div class="table-responsive">
                                    <table id="invoice_content_table" class="table table-vcenter card-table">
                                        <thead>
                                        <tr>
                                            <th>{trans key='user.name'}</th>
                                            <th>{trans key='user.price'}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        {foreach $invoice->content as $invoice_content}
                                            <tr>
                                                <td>{$invoice_content->name}</td>
                                                <td>{$invoice_content->price}</td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">{trans key='user.invoice_amount'}</div>
                            <div class="datagrid-content">{$invoice->price}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">{trans key='user.invoice_status'}</div>
                            <div class="datagrid-content">{$invoice->status}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">{trans key='user.create_time'}</div>
                            <div class="datagrid-content">{$invoice->create_time}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">{trans key='user.update_time'}</div>
                            <div class="datagrid-content">{$invoice->update_time}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">{trans key='user.payment_time'}</div>
                            <div class="datagrid-content">{$invoice->pay_time}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {include file='user/footer.tpl'}
