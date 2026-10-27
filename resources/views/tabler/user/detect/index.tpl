{include file='user/header.tpl'}

<!-- Audit rules are used to prevent DMCA and email Spam, not for building walls for users. -->
<div class="page-wrapper">
    <div class="container-xl">
        <div class="page-header d-print-none text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <span class="home-title">{trans key='user.detect_rules_title'}</span>
                    </h2>
                    <div class="page-pretitle my-3">
                        <span class="home-subtitle">{trans key='user.detect_rules_subtitle'}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>{trans key='user.name'}</th>
                                    <th>{trans key='common.description'}</th>
                                    <th>{trans key='user.detect_regex'}</th>
                                    <th>{trans key='common.type'}</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $rules as $rule}
                                    <tr>
                                        <td>#{$rule->id}</td>
                                        <td>{$rule->name}</td>
                                        <td>{$rule->text}</td>
                                        <td>{$rule->regex}</td>
                                        {if $rule->type === 1}
                                            <td>{trans key='user.detect_type_packet_plaintext'}</td>
                                        {/if}
                                        {if $rule->type === 2}
                                            <td>{trans key='user.detect_type_packet_hex'}</td>
                                        {/if}
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
