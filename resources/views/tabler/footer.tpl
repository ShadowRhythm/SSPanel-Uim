<div class="text-center mt-3 mb-3">
    <a href="/locale/zh_CN" class="text-muted me-2">中文</a>
    <a href="/locale/zh_TW" class="text-muted me-2">繁體中文</a>
    <a href="/locale/en_US" class="text-muted me-2">English</a>
    <a href="/locale/ja_JP" class="text-muted me-2">日本語</a>
    <a href="/locale/ru_RU" class="text-muted me-2">Русский</a>
    <a href="/locale/fr_FR" class="text-muted me-2">Français</a>
    <a href="/locale/pt_BR" class="text-muted">Português</a>
</div>

<div class="modal modal-blur fade" id="success-dialog" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-status bg-success"></div>
            <div class="modal-body text-center py-4">
                <i class="ti ti-circle-check icon mb-2 text-green icon-lg" style="font-size:3.5rem;"></i>
                <p id="success-message" class="text-secondary">{trans key='common.success'}</p>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col">
                            <a id="success-confirm" href="" class="btn w-100" data-bs-dismiss="modal">
                                {trans key='common.ok'}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-blur fade" id="fail-dialog" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-status bg-danger"></div>
            <div class="modal-body text-center py-4">
                <i class="ti ti-circle-x icon mb-2 text-danger icon-lg" style="font-size:3.5rem;"></i>
                <p id="fail-message" class="text-secondary">{trans key='common.error'}</p>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col">
                            <a href="" class="btn btn-danger w-100" data-bs-dismiss="modal">
                                {trans key='common.confirm'}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="//{$config['jsdelivr_url']}/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>

<script>
    let Modal = tabler.Modal || tabler.bootstrap.Modal;
    let successDialog = new Modal(document.getElementById('success-dialog'));
    let failDialog = new Modal(document.getElementById('fail-dialog'));

    htmx.on("htmx:afterRequest", function(evt) {
        if (evt.detail.xhr.getResponseHeader('HX-Redirect'))
        {
            return;
        }

        let res = JSON.parse(evt.detail.xhr.response);

        if (evt.detail.elt.id === 'send-verify-email') {
            document.getElementById('send-verify-email').disabled = true;
        }

        if (res.ret === 1) {
            document.getElementById("success-message").innerHTML = res.msg;
            successDialog.show();
        } else {
            document.getElementById("fail-message").innerHTML = res.msg;
            failDialog.show();
        }
    });
</script>

{include file='live_chat.tpl'}

{include file='telemetry.tpl'}

</body>

</html>
