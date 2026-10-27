<?php

/**
 * I18n Service tests using Pest
 */

use App\Services\I18n;
use Symfony\Component\Translation\Translator;

require_once __DIR__ . '/../../../app/predefine.php';

describe('I18n::trans', function () {
    it('returns existing translation for valid key and locale', function () {
        $key = 'lang_name';
        $lang = 'en_US';
        $expectedTranslation = 'English(Simplified)';

        $translation = I18n::trans($key, $lang);

        expect($translation)->toBe($expectedTranslation);
    });

    it('returns domain-based translation from modular directory', function () {
        $translationEn = I18n::trans('auth.login_title', 'en_US');
        $translationZh = I18n::trans('auth.login_title', 'zh_CN');

        expect($translationEn)->toBe('Sign in to User Center')
            ->and($translationZh)->toBe('登录到用户中心');
    });

    it('returns nested translation for valid key and locale', function () {
        $translation = I18n::trans('bot.user_not_bind', 'zh_CN');

        expect($translation)->toBe('你未绑定本站账号，你可以进入网站的 **资料编辑**，在右下方绑定你的账号。');
    });

    it('returns key when translation does not exist', function () {
        $key = 'non_existent_key';
        $lang = 'en_US';

        $translation = I18n::trans($key, $lang);

        expect($translation)->toBe($key);
    });

    it('replaces placeholder params in translation', function () {
        $translation = I18n::trans('bot.node_added', 'zh_CN', ['node_name' => 'HK-01']);

        expect($translation)->toBe('HK-01 已被添加');
    });
});

describe('I18n::isValidLocale', function () {
    it('validates supported locales', function () {
        expect(I18n::isValidLocale('en_US'))->toBeTrue()
            ->and(I18n::isValidLocale('zh_CN'))->toBeTrue()
            ->and(I18n::isValidLocale('invalid_XX'))->toBeFalse();
    });
});

describe('I18n::getCurrentLocale', function () {
    afterEach(function () {
        unset($_COOKIE['locale'], $_SERVER['HTTP_ACCEPT_LANGUAGE'], $_ENV['locale_auto_detect']);
        $_ENV['locale'] = 'zh_CN';
    });

    it('picks cookie locale when valid', function () {
        $_COOKIE['locale'] = 'ja_JP';

        expect(I18n::getCurrentLocale())->toBe('ja_JP');
    });

    it('picks accept-language when valid and auto detect enabled', function () {
        $_ENV['locale_auto_detect'] = true;
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9';

        expect(I18n::getCurrentLocale())->toBe('en_US');
    });

    it('falls back to default env locale when auto detect is disabled', function () {
        $_ENV['locale_auto_detect'] = false;
        $_ENV['locale'] = 'zh_CN';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9';

        expect(I18n::getCurrentLocale())->toBe('zh_CN');
    });
});

describe('I18n::mapLanguageTag', function () {
    it('maps language subtags correctly', function () {
        expect(I18n::mapLanguageTag('zh-CN'))->toBe('zh_CN')
            ->and(I18n::mapLanguageTag('zh-Hans'))->toBe('zh_CN')
            ->and(I18n::mapLanguageTag('zh-SG'))->toBe('zh_CN')
            ->and(I18n::mapLanguageTag('zh-TW'))->toBe('zh_TW')
            ->and(I18n::mapLanguageTag('zh-Hant'))->toBe('zh_TW')
            ->and(I18n::mapLanguageTag('zh-HK'))->toBe('zh_TW')
            ->and(I18n::mapLanguageTag('zh-MO'))->toBe('zh_TW')
            ->and(I18n::mapLanguageTag('ja'))->toBe('ja_JP')
            ->and(I18n::mapLanguageTag('ja-JP'))->toBe('ja_JP')
            ->and(I18n::mapLanguageTag('en'))->toBe('en_US')
            ->and(I18n::mapLanguageTag('en-US'))->toBe('en_US');
    });
});

describe('I18n::getLocaleList', function () {
    it('returns list of available locales', function () {
        $expectedLocales = ['en_US', 'ja_JP', 'zh_CN', 'zh_TW'];

        $locales = I18n::getLocaleList();

        expect($locales)->toBe($expectedLocales);
    });
});

describe('I18n::getTranslator', function () {
    it('returns translator instance with correct locale', function () {
        $lang = 'en_US';

        $translator = I18n::getTranslator($lang);

        expect($translator)
            ->toBeInstanceOf(Translator::class)
            ->and($translator->getLocale())->toBe($lang);
    });
});
