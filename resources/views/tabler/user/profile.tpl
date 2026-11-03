{include file='user/header.tpl'}

<div class="page-wrapper">
    <div class="container-xl">
        <div class="page-header d-print-none text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <span class="home-title">{trans key='user.profile_title'}</span>
                    </h2>
                    <div class="page-pretitle my-3">
                        <span class="home-subtitle">{trans key='user.profile_subtitle'}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">{trans key='user.account_email'}</div>
                            </div>
                            <div class="h1 mb-3">{$user->email|escape}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">{trans key='user.account_username'}</div>
                            </div>
                            <div class="h1 mb-3">{$user->user_name|escape}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">{trans key='user.registration_time'}</div>
                            </div>
                            <div class="h1 mb-3">{$user->reg_date}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">{trans key='user.total_traffic_used'}</div>
                            </div>
                            <div class="h1 mb-3">{$user->totalTraffic()}</div>
                        </div>
                    </div>
                </div>
            </div>
            {if $public_setting['subscribe_log']}
            <div class="row row-deck my-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{trans key='user.recent_sub_logs'}</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter text-nowrap card-table">
                                <thead>
                                    <tr>
                                        <th>{trans key='common.type'}</th>
                                        <th>{trans key='user.user_agent'}</th>
                                        <th>{trans key='user.ip_address'}</th>
                                        <th>{trans key='user.ip_location'}</th>
                                        <th>{trans key='common.time'}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach $subs as $sub}
                                    <tr>
                                        <td>{$sub->type|escape}</td>
                                        <td>{$sub->request_user_agent|escape}</td>
                                        <td>{$sub->request_ip|escape}</td>
                                        <td>{$sub->location|escape}</td>
                                        <td>{$sub->request_time|escape}</td>
                                    </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            {/if}
            <div class="row row-deck my-3">
                {if $public_setting['login_log']}
                <div class="col-md-6 col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{trans key='user.recent_login_logs'}</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter text-nowrap card-table">
                                <thead>
                                    <tr>
                                        <th>{trans key='user.ip_address'}</th>
                                        <th>{trans key='user.ip_location'}</th>
                                        <th>{trans key='common.time'}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach $logins as $login}
                                    <tr>
                                        <td>{$login->ip|escape}</td>
                                        <td>{$login->location|escape}</td>
                                        <td>{$login->datetime|escape}</td>
                                    </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                {/if}
                <div class="col-md-6 col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{trans key='user.current_online_ip'}</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter text-nowrap card-table">
                                <thead>
                                    <tr>
                                        <th>{trans key='user.ip_address'}</th>
                                        <th>{trans key='user.ip_location'}</th>
                                        <th>{trans key='user.node_name'}</th>
                                        <th>{trans key='user.last_online_time'}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach $ips as $ip}
                                    <tr>
                                        <td>{$ip->ip|escape}</td>
                                        <td>{$ip->location|escape}</td>
                                        <td>{$ip->node_name|escape}</td>
                                        <td>{$ip->last_time|escape}</td>
                                    </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

{include file='user/footer.tpl'}
