{include file='user/header.tpl'}

<style>
/* Animation classes for collapsible sections */
.collapsible-section {
    transition: all 0.35s ease;
    overflow: hidden;
}

.collapsible-section.collapsing {
    opacity: 0.3;
    transform: scale(0.98);
}

.collapsible-section.expanded {
    opacity: 1;
    transform: scale(1);
}

/* Client item hover effects */
.client-item:hover {
    border-color: var(--tblr-primary) !important;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Copy button feedback */
.copy.copied {
    background-color: var(--tblr-success) !important;
    border-color: var(--tblr-success) !important;
}

.recommended-section {
    background: color-mix(in oklab, var(--tblr-primary) 10%, transparent);
    border: 1px solid color-mix(in oklab, var(--tblr-primary) 20%, transparent);
}

.client-item {
    transition: all 0.3s;
}

.client-item:hover {
    background: var(--tblr-bg-surface-secondary);
}

.client-action-btn {
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 0.35rem 0.65rem;
    font-size: 0.8125rem;
    min-width: 0;
    transition: all 0.2s ease;
}

.client-action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
}

@media (max-width: 576px) {
    .recommended-section h4 {
        font-size: 1rem;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
    
    .client-action-btn {
        min-height: 38px;
    }
    
    .client-item {
        padding: 0.75rem !important;
    }
    
    .recommended-section .card-body {
        padding: 0.75rem;
    }
}

/* Accordion styles */
.accordion-button:not(.collapsed) {
    background: var(--tblr-primary-lt);
    color: var(--tblr-primary);
}

/* Sensitive information blur effect */
.spoiler {
    filter: blur(5px);
    transition: filter 0.3s;
}

.spoiler:hover,
.universal-sub-container:hover .spoiler {
    filter: none;
}

.universal-sub-container {
    background-color: var(--tblr-bg-forms);
    overflow-x: auto;
    white-space: nowrap;
    scrollbar-width: none;
}

.universal-sub-container::-webkit-scrollbar {
    display: none;
}
</style>

<div class="page-wrapper">
    <div class="container-xl">
        <div class="page-header d-print-none text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <span class="home-title">{trans key='user.dashboard_title'}</span>
                    </h2>
                    <div class="page-pretitle my-3">
                        <span class="home-subtitle">{trans key='user.dashboard_subtitle'}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <div class="row row-cards">
                        {foreach $info_cards as $card}
                        <div class="col-sm-6 col-lg-3">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <span class="bg-{$card.color} text-white avatar">
                                                <i class="ti {$card.icon} icon"></i>
                                            </span>
                                        </div>
                                        <div class="col">
                                            <div class="font-weight-medium">
                                                {$card.title}
                                            </div>
                                            <div class="text-secondary">
                                                {$card.value}
                                            </div>
                                        </div>
                                        {if isset($card.action_url)}
                                        <div class="col-auto">
                                            <a href="{$card.action_url}" class="btn btn-primary btn-icon">
                                                <i class="ti ti-plus icon"></i>
                                            </a>
                                        </div>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        </div>
                        {/foreach}
                    </div>
                </div>
                
                <div class="col-lg-6 col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{trans key='user.quick_setup'}</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h4 class="mb-3">
                                    <i class="ti ti-link"></i> {trans key='user.exclusive_sub_url'}
                                </h4>
                                <div class="input-group mb-2">
                                    <div class="form-control d-flex align-items-center text-nowrap universal-sub-container" id="universal-sub-link" style="user-select: all; cursor: text;">
                                        {if isset($subUrlPrefix) && $subUrlToken !== ''}
                                            <span>{$subUrlPrefix}</span><span class="spoiler">{$subUrlToken}</span>
                                        {else}
                                            <span>{$UniversalSub}</span>
                                        {/if}
                                    </div>
                                    <button class="btn btn-primary copy" data-clipboard-text="{$UniversalSub}">
                                        <i class="ti ti-copy"></i> {trans key='common.copy'}
                                    </button>
                                </div>
                                <p class="text-muted mb-0">
                                    <small>{trans key='user.sub_url_tip'}</small>
                                </p>
                            </div>

                            <div class="recommended-section p-3 bg-primary-lt rounded mb-3">
                                <h4 class="mb-3">
                                    <i class="ti ti-rocket"></i> 
                                    {trans key='user.recommended_client_for_os' os='<span id="detected-os" class="text-primary">Windows</span>'}
                                </h4>
                                <div class="row g-3" id="recommended-clients">
                                </div>
                            </div>

                            <div class="text-center">
                                <button class="btn btn-ghost-primary" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#all-platforms" aria-expanded="false">
                                    <i class="ti ti-package"></i> 
                                    {trans key='user.view_other_platforms'}
                                    <i class="ti ti-chevron-down ms-1"></i>
                                </button>
                            </div>
                            
                            <div class="collapse mt-3" id="all-platforms">
                                <div class="accordion" id="platform-accordion">
                                </div>
                                
                                <div class="mt-3 p-3 bg-secondary-lt rounded">
                                    <h5 class="mb-2">{trans key='user.advanced_sub_formats'}</h5>
                                    <div class="small text-muted mb-2">{trans key='user.advanced_sub_desc'}</div>
                                    <div class="btn-group btn-group-sm flex-wrap">
                                        <button class="btn btn-outline-secondary copy" data-clipboard-text="{$UniversalSub}/json">
                                            {trans key='user.json_format'}
                                        </button>
                                        <button class="btn btn-outline-secondary copy" data-clipboard-text="{$UniversalSub}/v2rayjson">
                                            V2Ray JSON
                                        </button>
                                        {if $public_setting['enable_ss_sub']}
                                        <button class="btn btn-outline-secondary copy" data-clipboard-text="{$UniversalSub}/sip008">
                                            SIP008
                                        </button>
                                        <button class="btn btn-outline-secondary copy" data-clipboard-text="{$UniversalSub}/ss">
                                            Shadowsocks
                                        </button>
                                        {/if}
                                        {if $public_setting['enable_v2_sub']}
                                        <button class="btn btn-outline-secondary copy" data-clipboard-text="{$UniversalSub}/v2ray">
                                            V2Ray
                                        </button>
                                        {/if}
                                        {if $public_setting['enable_trojan_sub']}
                                        <button class="btn btn-outline-secondary copy" data-clipboard-text="{$UniversalSub}/trojan">
                                            Trojan
                                        </button>
                                        {/if}
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <button class="btn btn-ghost-secondary w-100" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#connection-info" aria-expanded="false">
                                        <i class="ti ti-info-circle"></i> 
                                        {trans key='user.view_connection_info'}
                                        <i class="ti ti-chevron-down ms-1"></i>
                                    </button>
                                    <div class="collapse mt-2" id="connection-info">
                                        <div class="p-3 bg-light rounded">
                                            <div class="table-responsive">
                                                <table class="table table-sm mb-0">
                                                    <tbody>
                                                    <tr>
                                                        <td class="text-muted" style="width: 100px;">{trans key='user.port'}</td>
                                                        <td><code>{$user->port}</code></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">{trans key='user.connection_password'}</td>
                                                        <td><code class="spoiler">{$user->passwd}</code></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">{trans key='user.uuid'}</td>
                                                        <td><code class="spoiler" style="font-size: 0.8em;">{$user->uuid}</code></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">{trans key='user.encryption_method'}</td>
                                                        <td><code>{$user->method}</code></td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-sm-12">
                    <div class="row row-cards">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">{trans key='user.traffic_usage'}</h3>
                                    <div class="progress progress-separated mb-3">
                                        {if $user->LastusedTrafficPercent() < '1'}
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 1%"></div>
                                        {else}
                                        <div class="progress-bar bg-primary" role="progressbar"
                                             style="width: {$user->LastusedTrafficPercent()}%">
                                        </div>
                                        {/if}
                                        {if $user->TodayusedTrafficPercent() < '1'}
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 1%"></div>
                                        {else}
                                        <div class="progress-bar bg-success" role="progressbar"
                                             style="width: {$user->TodayusedTrafficPercent()}%"></div>
                                        {/if}
                                    </div>
                                    <div class="row">
                                        <div class="col-auto d-flex align-items-center pe-2">
                                            <span class="legend me-2 bg-primary"></span>
                                            <span>{trans key='user.past_usage' traffic=$user->LastusedTraffic()}</span>
                                        </div>
                                        <div class="col-auto d-flex align-items-center px-2">
                                            <span class="legend me-2 bg-success"></span>
                                            <span>{trans key='user.today_usage' traffic=$user->TodayusedTraffic()}</span>
                                        </div>
                                        <div class="col-auto d-flex align-items-center ps-2">
                                            <span class="legend me-2"></span>
                                            <span>{trans key='user.remaining_traffic' traffic=$user->unusedTraffic()}</span>
                                        </div>
                                    </div>
                                    <p class="my-3">
                                        {if $user->class === 0}
                                        {trans key='user.go_to'}
                                        <a href="/user/product">{trans key='user.shop'}</a>
                                        {trans key='user.to_buy_package'}
                                        {else}
                                        {trans key='user.account_expire_notice' class=$user->class days=$class_expire_days expire=$user->class_expire}
                                        {/if}
                                    </p>
                                </div>
                            </div>
                        </div>
                        {if $public_setting['traffic_log']}
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">{trans key='user.hourly_usage'}</h3>
                                    <div id="traffic-log"></div>
                                </div>
                            </div>
                        </div>
                        {/if}
                        {if $public_setting['enable_checkin']}
                        <div class="col-12">
                            <div class="card">
                                <div class="card-stamp">
                                    <div class="card-stamp-icon bg-green">
                                        <i class="ti ti-check"></i>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h3 class="card-title">{trans key='user.daily_checkin'}</h3>
                                    <p>
                                        {if $public_setting['checkin_min'] !== $public_setting['checkin_max']}
                                        {trans key='user.checkin_range_reward' min=$public_setting['checkin_min'] max=$public_setting['checkin_max']}
                                        {else}
                                        {trans key='user.checkin_fixed_reward' min=$public_setting['checkin_min']}
                                        {/if}
                                    </p>
                                    <p>
                                        {trans key='user.last_checkin_time' time="<code id='last-checkin-time'>{$user->lastCheckInTime()}</code>"}
                                    </p>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex">
                                        {if !$user->isAbleToCheckin()}
                                        <button id="check-in" class="btn btn-primary ms-auto" disabled>{trans key='user.already_checked_in'}</button>
                                        {else}
                                        {if $public_setting['enable_checkin_captcha']}
                                        {include file='captcha/div.tpl'}
                                        {/if}
                                        <button id="check-in" class="btn btn-primary ms-auto"
                                            hx-post="/user/checkin" hx-swap="none" hx-vals='js:{
                                            {if $public_setting['enable_checkin_captcha']}
                                            {include file='captcha/ajax.tpl'}
                                            {/if}
                                            }'>
                                            {trans key='user.checkin'}
                                        </button>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        </div>
                        {/if}
                        <div class="col-12">
                            <div class="card">
                                <div class="ribbon ribbon-top bg-yellow">
                                    <i class="ti ti-bell-ringing icon"></i>
                                </div>
                                <div class="card-body">
                                    <h3 class="card-title">
                                        {trans key='user.pinned_announcement'}
                                        {if $ann !== null}
                                        <span class="card-subtitle">{$ann->date}</span>
                                        {/if}
                                    </h3>
                                    <p class="text-secondary">
                                        {if $ann !== null}
                                        {$ann->content}
                                        {else}
                                        {trans key='user.no_announcement'}
                                        {/if}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {if $public_setting['enable_checkin_captcha'] && $user->isAbleToCheckin()}
        {include file='captcha/js.tpl'}
    {/if}

    {if $public_setting['traffic_log']}
    <script src="//{$config['jsdelivr_url']}/npm/@tabler/core@latest/dist/libs/apexcharts/dist/apexcharts.min.js"></script>
    <script>
        function getTrafficChartConfig(trafficData) {
            return {
                chart: {
                    type: "line",
                    fontFamily: "inherit",
                    height: '100%',
                    parentHeightOffset: 0,
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: false
                    }
                },
                stroke: {
                    curve: "smooth"
                },
                fill: {
                    opacity: 1
                },
                series: [
                    {
                        name: "{trans key='user.traffic_used_mb' escape='js'}",
                        data: trafficData
                    }
                ],
                tooltip: {
                    theme: "dark"
                },
                grid: {
                    padding: {
                        top: -20,
                        right: 0,
                        left: 0,
                        bottom: 0
                    },
                    strokeDashArray: 4
                },
                xaxis: {
                    title: {
                        text: "{trans key='user.hour' escape='js'}"
                    },
                    labels: {
                        padding: 0
                    },
                    tooltip: {
                        enabled: false
                    },
                    axisBorder: {
                        show: false
                    },
                    categories: [
                        "00", "01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11",
                        "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23"
                    ]
                },
                yaxis: {
                    title: {
                        text: "{trans key='user.traffic_used_mb' escape='js'}",
                        rotate: -90
                    },
                    labels: {
                        padding: 14
                    }
                },
                colors: ["#FF4500"],
                legend: {
                    show: false
                }
            };
        }
        
        function initTrafficChart() {
            const chartElement = document.getElementById('traffic-log');
            if (!chartElement || !window.ApexCharts) return;
            
            try {
                const chart = new ApexCharts(chartElement, getTrafficChartConfig({$traffic_logs}));
                chart.render();
            } catch (error) {
                console.error('Failed to initialize traffic chart:', error);
            }
        }
        
        document.addEventListener("DOMContentLoaded", function () {
            initTrafficChart();
        });
    </script>
    {/if}

    <script>
    window.APP_CONFIG = {
        enableR2Download: {if $config['enable_r2_client_download']}true{else}false{/if},
        universalSubUrl: "{$UniversalSub}",
        appName: "{$config['appName']}",
        enableSsSub: {if $public_setting['enable_ss_sub']}true{else}false{/if},
        enableV2Sub: {if $public_setting['enable_v2_sub']}true{else}false{/if},
        enableTrojanSub: {if $public_setting['enable_trojan_sub']}true{else}false{/if}
    };
    
    const platformIcons = {$platformIcons};

    const clientRecommendations = {$clientData};
    
    {literal}
    function detectOS() {
        const ua = navigator.userAgent;
        
        // 1. Check iOS devices first (iPhone, iPod, iPad)
        if (/iPhone|iPad|iPod/i.test(ua)) {
            return "iOS";
        }
        
        // iPadOS 13+ reports as Macintosh but has multi-touch screen
        if ((navigator.platform === 'MacIntel' || /Macintosh/i.test(ua)) && navigator.maxTouchPoints > 1) {
            return "iOS";
        }
        
        // 2. Android
        if (/Android/i.test(ua)) {
            return "Android";
        }
        
        // 3. Windows
        if (/Win/i.test(ua) || /Windows/i.test(ua)) {
            return "Windows";
        }
        
        // 4. macOS (Mac without touch screen)
        if (/Mac/i.test(ua)) {
            return "macOS";
        }
        
        // 5. Linux
        if (/Linux/i.test(ua)) {
            return "Linux";
        }
        
        return "Windows"; // default
    }
    

    const CONFIG = {
        ANIMATION_DURATION: 350,        // Animation duration (ms)
        FEEDBACK_TIMEOUT: 2000,         // Feedback timeout (ms)
        CLIPBOARD_SUCCESS_TEXT: '{/literal}{trans key="common.copied" escape="js"}{literal}',
        CLIPBOARD_ERROR_TEXT: '{/literal}{trans key="common.copy_manual_prompt" escape="js"}{literal}',
        BUTTONS: {
            download: { icon: 'ti-download', text: '{/literal}{trans key="user.client_download" escape="js"}{literal}', class: 'btn-primary' },
            downloadAppStore: { icon: 'ti-brand-appstore', text: '{/literal}{trans key="user.client_app_store" escape="js"}{literal}', class: 'btn-primary' },
            copy: { icon: 'ti-copy', text: '{/literal}{trans key="user.client_copy_sub" escape="js"}{literal}', class: 'btn-info copy' },
            import: { icon: 'ti-link', text: '{/literal}{trans key="user.client_import" escape="js"}{literal}', class: 'btn-success' },
            importRecommended: { icon: 'ti-rocket', text: '{/literal}{trans key="user.client_import" escape="js"}{literal}', class: 'btn-success' }
        }
    };
    
    function safeInit(fn, name) {
        try {
            fn();
        } catch (error) {
            console.error(`${name} failed to initialize:`, error);
        }
    }
    
    function createElement(tag, className, content) {
        const element = document.createElement(tag);
        if (className) element.className = className;
        if (content) element.textContent = content;
        return element;
    }
    
    function createIcon(iconClass) {
        const icon = createElement('i', 'ti ' + iconClass);
        return icon;
    }
    
    function createButton(type, options = {}) {
        const { client, url, isRecommended } = options;
        const btnConfig = CONFIG.BUTTONS[type];
        
        let config = { ...btnConfig };
        if (type === 'download' && client?.isAppStore) {
            config = CONFIG.BUTTONS.downloadAppStore;
        } else if (type === 'import' && isRecommended) {
            config = CONFIG.BUTTONS.importRecommended;
        }
        
        const btn = createElement(type === 'copy' ? 'button' : 'a', 'btn btn-sm flex-fill client-action-btn ' + config.class);
        
        if (type === 'copy') {
            btn.setAttribute('data-clipboard-text', url);
            btn.type = 'button';
        } else {
            btn.href = url;
            if (type === 'download' && client?.isAppStore) {
                btn.target = '_blank';
            }
        }
        
        btn.appendChild(createIcon(config.icon + ' me-1'));
        btn.appendChild(document.createTextNode(config.text));
        
        return btn;
    }
    
    function createActionButtons(client, urls, isRecommended = false) {
        const { downloadUrl, subUrl, importUrl } = urls;
        const container = createElement('div', 'd-flex flex-wrap gap-2 w-100 mt-2');
        
        const buttonConfigs = [
            { type: 'download', url: downloadUrl, needsClient: true },
            { type: 'copy', url: subUrl },
            { type: 'import', url: importUrl }
        ];
        
        buttonConfigs.forEach(btnConfig => {
            if (btnConfig.type !== 'copy' && !btnConfig.url) {
                return;
            }
            const options = {
                client: btnConfig.needsClient ? client : null,
                url: btnConfig.url,
                isRecommended
            };
            container.appendChild(createButton(btnConfig.type, options));
        });
        
        return container;
    }
    
    function generateClientHtml(client, isRecommended) {
        const config = window.APP_CONFIG;
        
        let downloadUrl = client.downloadUrl;
        if (!client.isAppStore && downloadUrl.startsWith('/clients/')) {
            downloadUrl = config.enableR2Download ? '/user' + downloadUrl : downloadUrl;
        }
        
        const subUrl = config.universalSubUrl + '/' + client.format;
        const importUrl = client.importUrl;
        const urls = { downloadUrl, subUrl, importUrl };
        
        const container = createElement('div', 'col-12');
        
        if (isRecommended) {
            const card = createElement('div', 'card shadow-sm');
            const cardBody = createElement('div', 'card-body p-3');
            
            const headerDiv = createElement('div', 'd-flex align-items-center justify-content-between mb-1');
            const title = createElement('h4', 'card-title mb-0 fw-bold', client.name);
            headerDiv.appendChild(title);
            if (client.format) {
                const badge = createElement('span', 'badge bg-blue-lt text-uppercase', client.format);
                headerDiv.appendChild(badge);
            }
            
            const desc = createElement('p', 'text-secondary small mb-2', client.description);
            const buttons = createActionButtons(client, urls, true);
            
            cardBody.appendChild(headerDiv);
            cardBody.appendChild(desc);
            cardBody.appendChild(buttons);
            card.appendChild(cardBody);
            container.appendChild(card);
        } else {
            const item = createElement('div', 'client-item p-3 border rounded mb-2');
            
            const headerDiv = createElement('div', 'd-flex align-items-center justify-content-between mb-1');
            const title = createElement('h5', 'mb-0 fw-bold', client.name);
            headerDiv.appendChild(title);
            if (client.format) {
                const badge = createElement('span', 'badge bg-blue-lt text-uppercase', client.format);
                headerDiv.appendChild(badge);
            }
            
            const desc = createElement('p', 'text-secondary small mb-2', client.description);
            const buttons = createActionButtons(client, urls, false);
            
            item.appendChild(headerDiv);
            item.appendChild(desc);
            item.appendChild(buttons);
            container.appendChild(item);
        }
        
        return container.outerHTML;
    }
    
    function initClientSelector() {
        const os = detectOS();
        document.getElementById('detected-os').textContent = os;
        
        const recommendations = clientRecommendations[os] || clientRecommendations["Windows"];
        const recommendedContainer = document.getElementById('recommended-clients');
        
        if (recommendedContainer) {
            recommendations.forEach(function(client) {
                const clientHtml = generateClientHtml(client, true);
            recommendedContainer.insertAdjacentHTML('beforeend', clientHtml);
            });
        }
        
        const accordionContainer = document.getElementById('platform-accordion');
        
        if (accordionContainer) {
            Object.keys(clientRecommendations).forEach(function(platform) {
                const clients = clientRecommendations[platform];
                const platformId = 'platform-' + platform.toLowerCase();
                const icon = platformIcons[platform] || CONFIG.BUTTONS.download.icon.replace('ti-', 'ti-device-');
                
                const accordionHtml = `
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#${platformId}">
                                <i class="ti ${icon} me-2"></i> ${platform}
                            </button>
                        </h2>
                        <div id="${platformId}" class="accordion-collapse collapse" 
                             data-bs-parent="#platform-accordion">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    ${clients.map(client => generateClientHtml(client, false)).join('')}
                                </div>
                            </div>
                        </div>
                    </div>`;
                    
                accordionContainer.insertAdjacentHTML('beforeend', accordionHtml.trim());
            });
        }
    }
    
    function initClipboard() {
        if (typeof ClipboardJS === 'undefined') {
            console.warn('ClipboardJS not loaded');
            return;
        }
        
        const clipboard = new ClipboardJS('.copy');
        
        clipboard.on('success', function(e) {
            e.clearSelection();
            const originalText = e.trigger.innerHTML;
            const checkIcon = createIcon('ti-check');
            e.trigger.innerHTML = '';
            e.trigger.appendChild(checkIcon);
            e.trigger.appendChild(document.createTextNode(' ' + CONFIG.CLIPBOARD_SUCCESS_TEXT));
            setTimeout(function() {
                e.trigger.innerHTML = originalText;
            }, CONFIG.FEEDBACK_TIMEOUT);
        });
        
        clipboard.on('error', function(e) {
            console.error('Copy failed:', e.action);
            alert(CONFIG.CLIPBOARD_ERROR_TEXT);
        });
    }
    
    function initCollapseAnimations() {
        const allPlatforms = document.getElementById('all-platforms');
        const recommendedSection = document.querySelector('.recommended-section');
        
        if (!allPlatforms || !recommendedSection) return;
        
        recommendedSection.classList.add('collapsible-section');
        
        allPlatforms.addEventListener('show.bs.collapse', function (e) {
            if (e.target !== allPlatforms) return;
            recommendedSection.classList.add('collapsing');
        });
        
        allPlatforms.addEventListener('hide.bs.collapse', function (e) {
            if (e.target !== allPlatforms) return;
            recommendedSection.classList.remove('collapsing');
            setTimeout(function() {
                recommendedSection.classList.add('expanded');
            }, CONFIG.ANIMATION_DURATION);
        });
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        safeInit(initClientSelector, 'Client selector');
        safeInit(initClipboard, 'Clipboard feature');
        safeInit(initCollapseAnimations, 'Collapse animation');
    });
    {/literal}
    </script>

    {include file='user/footer.tpl'}
