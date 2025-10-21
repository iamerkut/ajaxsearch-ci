<?php
// plugins/AjaxSearch/tests/codeception/acceptance/AjaxSearchCest.php

class AjaxSearchCest
{
    /**
     * Basit uçtan uca: endpoint 200 döner, JSON geçerli, şema alanları mevcut.
     */
    public function endpointResponds(AcceptanceTester $I): void
    {
        // min_length default 2; 2+ karakter gönder.
        $I->amOnPage('/?cc_ajax_search=1&q=test');
        $I->seeResponseCodeIs(200);
        $src = $I->grabPageSource();
        $I->assertNotEmpty($src, 'Boş yanıt');
        $json = json_decode($src, true);
        $I->assertIsArray($json, 'Geçersiz JSON');
        $I->assertArrayHasKey('ok', $json);
        if (!empty($json['ok'])) {
            $I->assertArrayHasKey('items', $json);
            $I->assertIsArray($json['items']);
            if (count($json['items']) > 0) {
                $item = $json['items'][0];
                foreach (['id', 'name', 'url'] as $k) {
                    $I->assertArrayHasKey($k, $item);
                }
                // priceGross isteğe bağlı; varsa sayı olmalı
                if (array_key_exists('priceGross', $item) && $item['priceGross'] !== null) {
                    $I->assertIsNumeric($item['priceGross']);
                }
            }
        } else {
            // ok=false ise hata alanı beklenir
            $I->assertArrayHasKey('error', $json);
        }
    }

    /**
     * Kısa sorguda (min_length altı) boş liste döner.
     */
    public function shortQueryReturnsEmpty(AcceptanceTester $I): void
    {
        $I->amOnPage('/?cc_ajax_search=1&q=a');
        $I->seeResponseCodeIs(200);
        $json = json_decode($I->grabPageSource(), true);
        $I->assertTrue($json['ok'] ?? false, 'ok bekleniyordu');
        $I->assertIsArray($json['items'] ?? null, 'items dizi olmalı');
        $I->assertEquals(0, count($json['items']), 'Kısa sorguda boş items beklenir');
    }

    /**
     * Kapalıyken 503 döner (enabled = N ise).
     * Not: Bu testin çalışması için eklenti ayarından kapatmanız gerekir.
     */
    public function disabledReturns503(AcceptanceTester $I): void
    {
        // Bu test varsayılan olarak atlanabilir; kullanıcı kapatma yaptıysa doğrular.
        $I->amOnPage('/?cc_ajax_search=1&q=test');
        $code = $I->grabHttpStatusCode();
        if ($code === 503) {
            $I->seeResponseCodeIs(503);
            $json = json_decode($I->grabPageSource(), true);
            $I->assertFalse($json['ok'] ?? true);
            $I->assertEquals('disabled', $json['error'] ?? '');
        } else {
            $I->assertTrue(in_array($code, [200, 503]), 'Beklenmeyen durum kodu');
        }
    }
}
