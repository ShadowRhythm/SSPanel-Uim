<script src="//{$config['jsdelivr_url']}/npm/jquery/dist/jquery.min.js"></script>

<div class="card-inner">
    <h4>
        {trans key='user.alipay_f2f'}
    </h4>
    <p class="card-heading"></p>
    <div id="f2f-qrcode"></div>
    <button class="btn btn-flat waves-attach" id="f2fpay-button" type="button" onclick="f2fpay();">
        {trans key='user.generate_payment_qrcode'}
    </button>
</div>

<script>
    let f2fQrcode = $('#f2fpay-button');

    function f2fpay() {
        $.ajax({
            type: "POST",
            url: "/user/payment/purchase/f2f",
            dataType: "json",
            data: {
                invoice_id: {$invoice->id},
            },
            success: (data) => {
                if (data.ret === 1) {
                    f2fQrcode.remove();
                    f2fQrcode.append('<div class="text-center"><p>{trans key='user.alipay_mobile_scan_pay' escape='js'}</p></div>');
                    new QRCode("f2f-qrcode", {
                        text: data.qrcode,
                        width: 200,
                        height: 200,
                        colorDark: '#000000',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.H,
                    });
                    f2fQrcode.append('<div class="text-center my-3"><p>{trans key='user.payment_success_refresh_prompt' escape='js'}</p></div>');
                    f2fQrcode.attr('href', data.qrcode);
                } else {
                    $('#fail-message').text(data.msg);
                    failDialog.show();
                }
            }
        })
    }
</script>
