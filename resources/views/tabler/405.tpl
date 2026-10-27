{include file='header.tpl'}

<body class="border-top-wide border-primary d-flex flex-column">
<div class="page page-center">
    <div class="container-tight my-auto">
        <div class="empty">
            <div class="empty-header">405</div>
            <p class="empty-title">{trans key='common.error_405_title'}</p>
            <p class="empty-subtitle text-secondary">
                Wanna try something stupid I see, now go back to the home page.
            </p>
            <div class="empty-action">
                <a href="/" class="btn btn-primary">
                    <i class="icon ti ti-chevron-left"></i>
                    {trans key='common.back_to_home'}
                </a>
            </div>
        </div>
    </div>
</div>

{include file='footer.tpl'}