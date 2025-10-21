# Testler (Codeception)

Hızlı uçtan uca doğrulama. Mağaza lokalde çalışıyorsa PhpBrowser ile endpoint'i test eder.

## Kurulum
1. Shop kökünde (veya plugin dizininde) Codeception yükleyin:
   ```bash
   composer require --dev codeception/codeception:^5
   ```

2. URL'i ayarlayın:
   `plugins/AjaxSearch/tests/codeception/codeception.yml` içindeki `url` değerini mağazanızın kök URL'i ile değiştirin (ör. `https://dev.mağaza.com`).

## Çalıştırma
Shop kökünden (veya bu klasörden) şu komutu çalıştırın:
```bash
vendor/bin/codecept run -c plugins/AjaxSearch/tests/codeception
```

## Notlar
- `disabledReturns503` testi, eklentiyi **Ayarlar → enabled = N** yaptığınızda 503 bekler; aksi halde 200 kabul edilir.
- Testler yalnızca endpoint ve JSON şemasını doğrular; sonuçların içeriği kataloğunuza bağlıdır.

---
## GitHub Actions (CI)
Bu depoda `/.github/workflows/ajaxsearch-ci.yml` workflow'u ile testler çalışır.
- Depo ayarlarına `AJAXSEARCH_BASE_URL` secret'ını ekleyin (ör. `https://dev.magaza.com`).
- Push/PR ile tetiklenir, PHP 8.1 ve 8.2 matrisinde koşturur.
- Hata durumunda Codeception `_output` artifact olarak yüklenir.
