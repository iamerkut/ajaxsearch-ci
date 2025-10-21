<?php
declare(strict_types=1);

class AjaxSearchCest
{
    public function endpointReturnsJson(\ApiTester $I): void
    {
        $q = getenv('SEARCH_QUERY') ?: 'a';
        $I->amOnPage('/?cc_ajax_search=1&q=' . urlencode($q));
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'ok' => 'boolean',
            'items' => 'array'
        ]);
    }
}
