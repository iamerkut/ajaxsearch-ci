<?php
// plugins/AjaxSearch/Bootstrap.php
declare(strict_types=1);

namespace Plugin\AjaxSearch;

use JTL\Shop;
use JTL\DB\DbInterface;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Catalog\Product\Artikel;

if (!\defined('PFAD_ROOT')) {
    exit();
}

class Bootstrap extends \JTL\Plugin\Bootstrapper
{
    private DbInterface $db;

    public function boot(): void
    {
        $this->db = Shop::Container()->getDB();

        // Lightweight endpoint: /?cc_ajax_search=1&q=term
        if ((int)Request::verifyGPDataString('cc_ajax_search') === 1) {
            $this->handleAjax();
            exit;
        }

        Shop::Smarty()->assign('ccAjaxSearchEnabled', true);
    }

    private function handleAjax(): void
    {
        \header('Content-Type: application/json; charset=utf-8');

        // Soft-kill switch
        if (($this->getPlugin()->getConf('enabled') ?? 'Y') !== 'Y') {
            http_response_code(503);
            echo \json_encode(['ok' => false, 'error' => 'disabled']);
            return;
        }

        $q = Request::verifyGPDataString('q') ?? '';
        $q = \trim(Text::removeSpecialChars($q));
        $minLen = (int)($this->getPlugin()->getConf('min_length') ?? 2);
        $limit  = \max(1, \min(50, (int)($this->getPlugin()->getConf('limit') ?? 8)));

        if (\mb_strlen($q) < $minLen) {
            echo \json_encode(['ok' => true, 'items' => []]);
            return;
        }

        try {
            $like = '%' . $this->db->escape($q) . '%';

            // Fast id preselect; keep object construction limited to $limit.
            $sql = "
                SELECT ta.kArtikel AS id
                FROM tartikel ta
                WHERE ta.nAktiv = 1
                  AND ta.nInvisible = 0
                  AND ta.cName LIKE :like
                ORDER BY ta.cName ASC
                LIMIT :lim
            ";
            /** @var array<int,object> $rows */
            $rows = $this->db->getObjects($sql, ['like' => $like, 'lim' => $limit]);

            $langID = (int)Shop::getLanguageID();
            $currency = Shop::getCurrency()->getCode();

            $items = [];
            foreach ($rows as $r) {
                $id = (int)$r->id;

                // Use Artikel to resolve URL, images, price consistently in 5.6
                $p = new Artikel($id, true, $langID);

                // Skip invalid or not loaded products defensively
                if (empty($p->kArtikel) || (int)$p->kArtikel !== $id) {
                    continue;
                }

                // URLs
                $url = $p->cURLFull ?? (Shop::getURL() . '/?a=' . $id);

                // Image (first available)
                $image = '';
                if (!empty($p->Bilder) && \is_array($p->Bilder)) {
                    $first = $p->Bilder[0] ?? null;
                    $image = $first->cURLMini
                        ?? $first->cURLKlein
                        ?? $first->cURLNormal
                        ?? '';
                }

                // Price (gross) if available
                $priceGross = null;
                if (isset($p->Preise) && isset($p->Preise->fVKBrutto)) {
                    $priceGross = (float)$p->Preise->fVKBrutto;
                }

                $items[] = [
                    'id'         => $id,
                    'name'       => (string)($p->cName ?? ''),
                    'url'        => (string)$url,
                    'image'      => (string)$image,
                    'priceGross' => $priceGross,
                    'currency'   => (string)$currency,
                    'sku'        => (string)($p->cArtNr ?? ''),
                    'stock'      => (int)($p->nIstBestand ?? 0),
                ];
            }

            echo \json_encode(['ok' => true, 'items' => $items], \JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            Shop::Container()->getLogService()->error('AjaxSearch error: ' . $e->getMessage());
            http_response_code(500);
            echo \json_encode(['ok' => false, 'error' => 'server_error']);
        }
    }
}
