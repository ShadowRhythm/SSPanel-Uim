{include file='admin/header.tpl'}

<script src="//{$config['jsdelivr_url']}/npm/jsoneditor@latest/dist/jsoneditor.min.js"></script>
<link href="//{$config['jsdelivr_url']}/npm/jsoneditor@latest/dist/jsoneditor.min.css" rel="stylesheet" type="text/css">

<div class="page-wrapper">
    <div class="container-xl">
        <div class="page-header d-print-none text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <span class="home-title">节点 #{$node->id}</span>
                    </h2>
                    <div class="page-pretitle my-3">
                        <span class="home-subtitle">编辑节点信息</span>
                    </div>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a id="save-node" href="#" class="btn btn-primary">
                            <i class="icon ti ti-device-floppy"></i>
                            保存
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-md-6 col-sm-12">
                    <div class="card">
                        <div class="card-header card-header-light">
                            <h3 class="card-title">基础信息</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">名称</label>
                                <div class="col">
                                    <input id="name" type="text" class="form-control" value="{$node->name}">
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">连接地址</label>
                                <div class="col">
                                    <input id="server" type="text" class="form-control" value="{$node->server}">
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">IPv4地址</label>
                                <div class="col">
                                    <input type="text" class="form-control" value="{$node->ipv4}" disabled>
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">IPv6地址</label>
                                <div class="col">
                                    <input type="text" class="form-control" value="{$node->ipv6}" disabled>
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">流量倍率</label>
                                <div class="col">
                                    <input id="traffic_rate" type="text" class="form-control"
                                           value="{$node->traffic_rate}">
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">接入类型</label>
                                <div class="col">
                                    <select id="sort" class="col form-select" value="{$node->sort}">
                                        <option value="14" {if $node->sort === 14}selected{/if}>Trojan</option>
                                        <option value="11" {if $node->sort === 11}selected{/if}>Vmess</option>
                                        <option value="2" {if $node->sort === 2}selected{/if}>TUIC</option>
                                        <option value="4" {if $node->sort === 4}selected{/if}>Hysteria2</option>
                                        <option value="5" {if $node->sort === 5}selected{/if}>AnyTLS</option>
                                        <option value="1" {if $node->sort === 1}selected{/if}>Shadowsocks2022</option>
                                        <option value="0" {if $node->sort === 0}selected{/if}>Shadowsocks</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">自定义配置</label>
                                <div id="custom_config"></div>
                                <label class="form-label col-form-label">
                                    请参考
                                    <a href="https://docs.sspanel.io/docs/configuration/nodes" target="_blank">
                                        节点自定义配置文档
                                    </a>
                                    修改节点自定义配置
                                </label>
                            </div>
                            <div class="form-group mb-3 row">
                                <span class="col">显示此节点</span>
                                <span class="col-auto">
                                    <label class="form-check form-check-single form-switch">
                                        <input id="type" class="form-check-input" type="checkbox" {if $node->type}checked="" {/if}>
                                    </label>
                                </span>
                            </div>
                            <div class="hr-text">
                                <span>动态倍率</span>
                            </div>
                            <div class="form-group mb-3 row">
                                <span class="col">启用动态流量倍率</span>
                                <span class="col-auto">
                                    <label class="form-check form-check-single form-switch">
                                        <input id="is_dynamic_rate" class="form-check-input" type="checkbox" {if $node->is_dynamic_rate}checked="" {/if}>
                                    </label>
                                </span>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">动态流量倍率计算方式</label>
                                <div class="col">
                                    <select id="dynamic_rate_type" class="col form-select" value="{$node->dynamic_rate_type}">
                                        <option value="0" {if $node->dynamic_rate_type === 0}selected{/if}>Logistic</option>
                                        <option value="1" {if $node->dynamic_rate_type === 1}selected{/if}>Linear</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">最大倍率</label>
                                <div class="col">
                                    <input id="max_rate" type="text" class="form-control" value="{$node->max_rate}">
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">最大倍率时间（时）</label>
                                <div class="col">
                                    <input id="max_rate_time" type="text" class="form-control" value="{$node->max_rate_time}">
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">最小倍率</label>
                                <div class="col">
                                    <input id="min_rate" type="text" class="form-control" value="{$node->min_rate}">
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">最小倍率时间（时）</label>
                                <div class="col">
                                    <input id="min_rate_time" type="text" class="form-control" value="{$node->min_rate_time}">
                                </div>
                                <label class="form-label col-form-label">
                                    最大倍率时间必须大于最小倍率时间，否则将不会生效
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-12">
                    <div class="card">
                        <div class="card-header card-header-light">
                            <h3 class="card-title">其他信息</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">等级</label>
                                <div class="col">
                                    <input id="node_class" type="text" class="form-control" value="{$node->node_class}">
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">组别</label>
                                <div class="col">
                                    <input id="node_group" type="text" class="form-control" value="{$node->node_group}">
                                </div>
                            </div>
                            <div class="hr-text">
                                <span>流量设置</span>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">已用流量</label>
                                <div class="col">
                                    <input id="node_bandwidth" type="text" class="form-control"
                                           value="{$node->node_bandwidth}" disabled="">
                                </div>
                                <div class="col-auto">
                                    <button id="reset-bandwidth" class="btn btn-red">重置</button>
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">可用流量 (GB)</label>
                                <div class="col">
                                    <input id="node_bandwidth_limit" type="text" class="form-control"
                                           value="{$node->node_bandwidth_limit}">
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">流量重置日</label>
                                <div class="col">
                                    <input id="bandwidthlimit_resetday" type="text" class="form-control"
                                           value="{$node->bandwidthlimit_resetday}">
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">速率限制 (Mbps)</label>
                                <div class="col">
                                    <input id="node_speedlimit" type="text" class="form-control"
                                           value="{$node->node_speedlimit}">
                                </div>
                            </div>
                            <div class="hr-text">
                                <span>高级选项</span>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">节点通讯密钥</label>
                                <input type="text" class="form-control" id="password" value="{$node->password}"
                                       disabled="">
                                <div class="row my-3">
                                    <div class="col">
                                        <button id="reset-password" class="btn btn-red">重置</button>
                                        <button id="copy-password" class="copy btn btn-primary"
                                                data-clipboard-text="{$node->password}">
                                            复制
                                        </button>
                                    </div>
                                </div>
                                <label class="form-label col-form-label">
                                    通讯密钥用于 NodeAPI 鉴权，如需更改请点击重置
                                </label>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-12 col-md-3 col-form-label">后端版本</label>
                                <div class="col-12 col-md-9 col-form-label">
                                    <div class="d-flex flex-wrap align-items-center">
                                        <div class="me-3">
                                            {if $node->backend_version !== ''}
                                                <span class="badge bg-green-lt fs-4">{$node->backend_version}</span>
                                            {else}
                                                <span class="text-muted">暂未上报（需后端支持）</span>
                                            {/if}
                                        </div>
                                        <div class="d-flex gap-2 flex-grow-1 flex-md-grow-0 w-100 w-md-auto mt-2 mt-md-0">
                                            <button id="trigger-upgrade" class="btn btn-warning flex-fill flex-md-grow-0 text-nowrap">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                                     viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                                     stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"/>
                                                    <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"/>
                                                </svg>
                                                一键更新
                                            </button>
                                            <button id="trigger-restart" class="btn btn-danger flex-fill flex-md-grow-0 text-nowrap">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                                     viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                                     stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M19.95 11a8 8 0 1 0 -.5 4m.5 5v-5h-5"/>
                                                </svg>
                                                重启后端
                                            </button>
                                        </div>
                                    </div>
                                    <small class="form-hint mt-2">后端将在下次心跳（约 60s 内）接收指令并执行更新或重启</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let clipboard = new ClipboardJS('.copy');
    clipboard.on('success', function (e) {
        $('#success-message').text('已复制到剪切板');
        successDialog.show();
    });

    const container = document.getElementById('custom_config');
    let options = {
        modes: ['code', 'tree'],
    };
    const editor = new JSONEditor(container, options);
    editor.set({$node->custom_config})

    $("#reset-bandwidth").click(function () {
        $.ajax({
            url: '/admin/node/{$node->id}/reset_bandwidth',
            type: 'POST',
            dataType: "json",
            success: function (data) {
                if (data.ret === 1) {
                    $('#success-message').text(data.msg);
                    successDialog.show();
                } else {
                    $('#fail-message').text(data.msg);
                    failDialog.show();
                }
            }
        })
    });

    $("#trigger-upgrade").click(function () {
        $.ajax({
            url: '/admin/node/{$node->id}/trigger_upgrade',
            type: 'POST',
            dataType: "json",
            success: function (data) {
                if (data.ret === 1) {
                    $('#success-message').text(data.msg);
                    successDialog.show();
                } else {
                    $('#fail-message').text(data.msg);
                    failDialog.show();
                }
            }
        });
    });

    $("#trigger-restart").click(function () {
        $.ajax({
            url: '/admin/node/{$node->id}/trigger_restart',
            type: 'POST',
            dataType: "json",
            success: function (data) {
                if (data.ret === 1) {
                    $('#success-message').text(data.msg);
                    successDialog.show();
                } else {
                    $('#fail-message').text(data.msg);
                    failDialog.show();
                }
            }
        });
    });

    $("#reset-password").click(function () {
        $.ajax({
            url: '/admin/node/{$node->id}/reset_password',
            type: 'POST',
            dataType: "json",
            success: function (data) {
                if (data.ret === 1) {
                    $('#success-message').text(data.msg);
                    successDialog.show();
                } else {
                    $('#fail-message').text(data.msg);
                    failDialog.show();
                }
            }
        })
    });

    $("#save-node").click(function () {
        $.ajax({
            url: '/admin/node/{$node->id}',
            type: 'PUT',
            dataType: "json",
            data: {
                {foreach $update_field as $key}
                {$key}: $('#{$key}').val(),
                {/foreach}
                type: $("#type").is(":checked"),
                is_dynamic_rate: $("#is_dynamic_rate").is(":checked"),
                custom_config: JSON.stringify(editor.get()),
            },
            success: function (data) {
                if (data.ret === 1) {
                    $('#success-message').text(data.msg);
                    successDialog.show();
                    window.setTimeout("location.href=top.document.referrer", {$config['jump_delay']});
                } else {
                    $('#fail-message').text(data.msg);
                    failDialog.show();
                }
            }
        })
    });
</script>

{include file='admin/footer.tpl'}
