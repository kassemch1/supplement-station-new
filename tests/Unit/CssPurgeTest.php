<?php

namespace Tests\Unit;

use App\Support\CssPurge;
use PHPUnit\Framework\TestCase;

// The unused-rule removal behind the admin.css bundle (config/assets.php 'purge')
class CssPurgeTest extends TestCase
{
    private function purge(string $css, array $words, array $safelist = []): string
    {
        return (new CssPurge(array_fill_keys($words, true), $safelist))->purge($css);
    }

    public function test_keeps_selectors_whose_classes_and_ids_are_used(): void
    {
        $css = '.used{color:red}.unused{color:blue}#menu .used{top:0}#gone{top:1px}a{color:green}';

        $out = $this->purge($css, ['used', 'menu']);

        $this->assertStringContainsString('.used{color:red}', $out);
        $this->assertStringContainsString('#menu .used{top:0}', $out);
        $this->assertStringContainsString('a{color:green}', $out);
        $this->assertStringNotContainsString('unused', $out);
        $this->assertStringNotContainsString('#gone', $out);
    }

    public function test_drops_only_the_unused_selectors_of_a_list(): void
    {
        $out = $this->purge('.a,.b:not(.c),.d[data-x=".e"]{margin:0}', ['b', 'd']);

        $this->assertStringContainsString('.b:not(.c),.d[data-x=".e"]{margin:0}', $out);
        $this->assertStringNotContainsString('.a,', $out);
    }

    public function test_filters_inside_media_queries_and_drops_empty_ones(): void
    {
        $css = '@media (max-width:767px){.a{top:0}.b{top:1px}}@media print{.c{top:0}}';

        $out = $this->purge($css, ['a']);

        $this->assertStringContainsString('@media (max-width:767px){.a{top:0}}', $out);
        $this->assertStringNotContainsString('print', $out);
    }

    public function test_safelist_keeps_classes_built_from_pieces(): void
    {
        $out = $this->purge('.badge-success{color:green}.badge-x{color:red}.other{color:blue}', [], ['/^badge-/']);

        $this->assertStringContainsString('.badge-success', $out);
        $this->assertStringContainsString('.badge-x', $out);
        $this->assertStringNotContainsString('.other', $out);
    }

    public function test_fonts_and_animations_only_when_something_kept_uses_them(): void
    {
        $css = "/*! licence */@font-face{font-family:'Icons';src:url(a.woff)}@font-face{font-family:Other;src:url(b.woff)}"
            . '.i{font:14px/1 Icons;animation:spin 1s}.x{font-family:Other;animation:pulse 1s}'
            . '@keyframes spin{from{opacity:0}to{opacity:1}}@-webkit-keyframes pulse{0%{top:0}}'
            . "@import url('https://fonts.example/css');@charset \"UTF-8\";";

        $out = $this->purge($css, ['i']);

        $this->assertStringStartsWith('/*! licence */', $out);
        $this->assertStringContainsString("font-family:'Icons'", $out);
        $this->assertStringContainsString('@keyframes spin{from{opacity:0}to{opacity:1}}', $out);
        $this->assertStringNotContainsString('Other', $out);
        $this->assertStringNotContainsString('pulse', $out);
        $this->assertStringNotContainsString('@import', $out);
        $this->assertStringNotContainsString('@charset', $out);
    }

    public function test_a_comment_before_a_rule_does_not_hide_it(): void
    {
        $out = $this->purge('/* note */@font-face{font-family:Kept;src:url(k.woff)}/*! x */.k{font-family:Kept}', ['k']);

        $this->assertStringContainsString('@font-face{font-family:Kept', $out);
        $this->assertStringContainsString('.k{font-family:Kept}', $out);
    }

    public function test_braces_and_commas_inside_strings_and_urls(): void
    {
        $css = '.q::before{content:"{,}"}.u{background:url("data:image/svg+xml,%3csvg%3e{}")}.no{top:0}';

        $out = $this->purge($css, ['q', 'u']);

        $this->assertStringContainsString('.q::before{content:"{,}"}', $out);
        $this->assertStringContainsString('.u{background:url("data:image/svg+xml,%3csvg%3e{}")}', $out);
        $this->assertStringNotContainsString('.no', $out);
    }
}
