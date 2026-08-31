{include file='user/header.tpl'}

<div class="page-wrapper">
    <div class="container-xl">
        <div class="page-header d-print-none text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title"><span class="home-title">流媒体解锁</span></h2>
                    <div class="page-pretitle my-3">
                        <span class="home-subtitle">查看节点最近一天的流媒体解锁情况</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                {if count($results) > 0}
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter text-nowrap">
                            <thead>
                                <tr>
                                    <th>节点</th>
                                    {foreach $headers as $header}<th>{$header}</th>{/foreach}
                                    <th>更新时间</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach $results as $result}
                                    <tr>
                                        <td>{$result['node_name']}</td>
                                        {foreach $headers as $header}
                                            <td><span class="media-result">{$result['unlock_item'][$header]|default:'-'}</span></td>
                                        {/foreach}
                                        <td>{$result['created_at']|date_format:"%Y-%m-%d %H:%M:%S"}</td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                {else}
                    <div class="card-body text-secondary">最近一天暂无节点上报的流媒体检测数据。</div>
                {/if}
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.media-result').forEach(function (element) {
        const value = element.textContent;
        if (value.includes('Yes')) element.classList.add('badge', 'bg-green-lt');
        if (value.includes('DNS')) element.classList.add('badge', 'bg-cyan-lt');
        if (value.includes('No')) element.classList.add('badge', 'bg-red-lt');
        if (value.includes('Only')) element.classList.add('badge', 'bg-purple-lt');
        if (value.includes('Failed')) element.classList.add('badge', 'bg-yellow-lt');
    });
</script>

{include file='user/footer.tpl'}
