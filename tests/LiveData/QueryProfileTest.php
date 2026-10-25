<?php

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// Logs query count/time per page and EXPLAINs each distinct SELECT to spot full scans and N+1 patterns.
class QueryProfileTest extends TestCase
{
    use DatabaseTransactions;

    public function test_profile(): void
    {
        $product = DB::table('product_options')->value('product_id');
        $order = DB::table('orders')->value('id');
        $pages = [
            ['/', false], ['/shop', false], ['/shop?category-sidebar=1&orderby=low-to-high', false], ['/shop?shop-search=whey', false],
            [$this->productUrl($product), false], ['/api/cart', false], ['/api/recent-purchases', false], ['/cart', false], ['/checkout', false],
            ['/admin', true], ['/admin/orders/pending', true], ['/admin/orders/delivered', true], ["/admin/orders/$order/details", true],
            ['/manageProducts', true], ['/admin/coupons', true], ['/admin/subscribers', true],
        ];

        // Put something in the cart so cart pages do real work
        $sel = DB::table('product_options')->join('options', 'options.id', '=', 'product_options.option_id')
            ->where('product_id', $product)->get()->groupBy('option_name')->map(fn ($g) => $g->first()->option_value)->all();
        $this->postJson('/cart/add', ['product_id' => $product, 'quantity' => 1, 'selected_options' => json_encode($sel)]);

        $scans = [];
        foreach ($pages as [$url, $admin]) {
            $queries = [];
            DB::flushQueryLog();
            DB::enableQueryLog();
            $t = microtime(true);
            $admin ? $this->actingAs(User::first())->get($url) : $this->get($url);
            $ms = (microtime(true) - $t) * 1000;
            $log = DB::getQueryLog();
            DB::disableQueryLog();

            $shapes = [];
            foreach ($log as $q) {
                $shape = preg_replace('/\s+/', ' ', $q['query']);
                $shapes[$shape] = ($shapes[$shape] ?? 0) + 1;
            }
            $dbms = array_sum(array_column($log, 'time'));
            fwrite(STDOUT, sprintf("\n### %-45s %3d queries  db %6.1fms  total %6.1fms\n", $url, count($log), $dbms, $ms));
            arsort($shapes);
            foreach ($shapes as $shape => $n) {
                if ($n > 1) fwrite(STDOUT, sprintf("   x%-3d %s\n", $n, substr($shape, 0, 160)));
            }
            foreach ($log as $q) {
                if (!str_starts_with(strtolower(ltrim($q['query'])), 'select')) continue;
                $key = preg_replace('/\s+/', ' ', $q['query']);
                if (isset($scans[$key])) { $scans[$key]['pages'][$url] = true; continue; }
                try {
                    foreach (DB::select('explain '.$q['query'], $q['bindings']) as $row) {
                        $row = (array) $row;
                        if (in_array($row['type'], ['ALL', 'index']) && ($row['rows'] ?? 0) > 20) {
                            $scans[$key] = ['table' => $row['table'], 'rows' => $row['rows'], 'extra' => $row['Extra'] ?? '', 'pages' => [$url => true]];
                        }
                    }
                } catch (\Throwable $e) {}
            }
        }

        fwrite(STDOUT, "\n\n=== Full scans (EXPLAIN type ALL/index, >20 rows) ===\n");
        foreach ($scans as $sql => $s) {
            fwrite(STDOUT, sprintf("[%s ~%d rows] %s\n   %s\n   pages: %s\n", $s['table'], $s['rows'], $s['extra'], substr($sql, 0, 220), implode(', ', array_keys($s['pages']))));
        }
        $this->assertTrue(true);
    }
}
