<div class="card-inner">
    <h4>
        <img src="/images/tether.svg"
             style="height:22px;vertical-align:middle;margin-right:6px;" alt="">
        {trans key='user.bepusdt'}
    </h4>
    <p class="card-heading text-muted" style="font-size:.85rem;margin-bottom:12px;">
        {trans key='user.bepusdt_desc'}
    </p>
    <form class="bepusdt mt-2" name="bepusdt" method="post">
        <button class="btn btn-primary w-100 waves-attach"
                hx-post="/user/payment/purchase/bepusdt" hx-swap="none"
                hx-vals='js:{
                    price: {$invoice->price},
                    invoice_id: {$invoice->id},
                    type: "bepusdt",
                    redir: window.location.href
                }'>
            <i class="icon ti ti-coin"></i> {trans key='user.bepusdt_pay'}
        </button>
    </form>
</div>
