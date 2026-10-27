<?php

declare(strict_types=1);

return [
    // Page titles and headings
    'login_title' => 'ユーザーセンターにログイン',
    'register_title' => '新規アカウント登録',
    'forgot_password_title' => 'パスワードをお忘れの方',
    'reset_password_title' => '新しいパスワードの設定',
    'mfa_title' => '2段階認証',

    // Form labels and placeholders
    'email' => 'メールアドレス',
    'email_placeholder' => 'メールアドレス（Outlook、Gmail、iCloudなどを推奨）',
    'registered_email' => '登録メールアドレス',
    'password' => 'パスワード',
    'password_placeholder' => 'パスワード',
    'confirm_password' => 'パスワード（確認）',
    'confirm_password_placeholder' => 'パスワードを再入力',
    'new_password' => '新しいパスワード',
    'new_password_placeholder' => '新しいパスワードを入力してください',
    'confirm_new_password' => '新しいパスワード（確認）',
    'confirm_new_password_placeholder' => '新しいパスワードを再入力してください',
    'nickname' => 'ニックネーム',
    'invite_code_optional' => '招待コード（任意）',
    'invite_code_required' => '招待コード（必須）',
    'email_verify_code' => 'メール認証コード',
    'email_verify_code_placeholder' => '認証コード',

    // Buttons
    'btn_login' => 'ログイン',
    'btn_webauthn_login' => 'WebAuthn でログイン',
    'btn_register' => '新規アカウント登録',
    'btn_get_code' => 'コードを取得',
    'btn_send_reset_email' => 'メールを送信',
    'btn_reset_password' => 'リセット',
    'btn_submit' => '送信',
    'btn_fido_verify' => 'FIDO2 で認証',

    // Checkboxes and links
    'remember_me' => 'このデバイスを記憶する',
    'forgot_password' => 'パスワードをお忘れですか？',
    'no_account' => 'アカウントをお持ちではありませんか？',
    'click_to_register' => '新規登録はこちら',
    'already_have_account' => 'すでにアカウントをお持ちですか？',
    'click_to_login' => 'ログインはこちら',
    'agree_terms_prefix' => '次の内容に同意します:',
    'terms_and_privacy' => '利用規約およびプライバシーポリシー',

    // Hints and notices
    'login_upgrade_notice' => 'システムがアップグレードされました。パスワードが正しくないと表示される場合は、パスワードをリセットしてからログインしてください！',
    'reset_password_desc' => '登録されたメールアドレスに、パスワード再設定用リンクを記載したメールを送信します。',
    'verify_code_tip' => '認証コードが届かない場合：推奨メールプロバイダー（Outlook、Gmail、iCloud、ProtonMailなど）に変更するか、迷惑メールフォルダを確認して当サービスからのメールを受信トレイに移動（ホワイトリスト登録）してください！',
    'reset_email_tip' => 'メールが届かない場合は、迷惑メールフォルダを確認して当サービスからのメールを受信トレイに移動（ホワイトリスト登録）してください！',
    'registration_closed' => '現在は新規登録を受け付けていません。後日改めてご確認ください。',
    'mfa_notice' => 'アカウントの2段階認証が有効になっています。セキュリティ保護のため、追加の本人確認を完了してください。',
];
