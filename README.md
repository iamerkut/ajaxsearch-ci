# AjaxSearch (JTL-Shop 5.6)

Debounce’lu, klavye destekli AJAX arama. Ek rota yok: `?cc_ajax_search=1&q=...`.

## Kurulum
1. Klasörü `plugins/AjaxSearch/` olarak yükleyin.
2. Backoffice → Plugins → AjaxSearch → **Install** & **Activate**.
3. Temanızda arama alanı göstermek istediğiniz yere ekleyin:

```smarty
{include file="plugin:AjaxSearch/frontend/templates/searchbar.tpl"}
```

## Ayarlar
- **enabled**: Aç/Kapa
- **min_length**: Minimum karakter (default 2)
- **limit**: Öneri sayısı (default 8)

## Notlar
- URL, resim ve fiyat çözümlemesi `\JTL\Catalog\Product\Artikel` üzerinden yapılır (5.6 uyumlu).
- Sorgu `tartikel.cName LIKE` ile id listeler, sonra en fazla `limit` adet ürünü detaylandırır.
- Performans için limit düşük tutulmalı (8–10 öneri idealdir).


---
## Testler
### PHPUnit
```bash
composer install
./vendor/bin/phpunit -c tests/phpunit/phpunit.xml
```
### Codeception (API)
```bash
cp tests/codeception/.env.example tests/codeception/.env
# .env içindeki BASE_URL ve SEARCH_QUERY değerlerini düzenleyin
./vendor/bin/codecept run -c tests/codeception/codeception.yml api
```
