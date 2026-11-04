{if $public_setting['captcha_provider'] === 'turnstile'}
    turnstile: (document.getElementsByName("cf-turnstile-response")[0] ? document.getElementsByName("cf-turnstile-response")[0].value : ""),
{/if}
{if $public_setting['captcha_provider'] === 'geetest'}
    geetest: (typeof geetest_result !== "undefined" ? geetest_result : ""),
{/if}
{if $public_setting['captcha_provider'] === 'hcaptcha'}
    hcaptcha: (window.hcaptcha ? hcaptcha.getResponse() : ""),
{/if}
{if $public_setting['captcha_provider'] === 'recaptcha_enterprise'}
    recaptcha_enterprise: (window.grecaptcha && grecaptcha.enterprise ? grecaptcha.enterprise.getResponse() : ""),
{/if}
