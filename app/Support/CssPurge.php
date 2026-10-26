<?php

namespace App\Support;

// Removes the CSS rules a set of pages can never use, for `php artisan assets:build` (config/assets.php,
// 'purge'). A selector is kept when every class and id in it appears as a word somewhere in the scanned
// files (views, controllers, the page scripts and the plugins' own JS, which add their classes at run
// time) or matches a safelist pattern; a rule goes when none of its selectors are kept. Element and
// attribute selectors are always kept. @font-face and @keyframes go when nothing kept refers to them,
// @import and @charset always go (the bundle is one file and loads no fonts from other sites).
// Meant for minified CSS; comments are dropped except the licence ones (/*! */), which go to the top.
class CssPurge
{
    /** @param array<string, true> $words  @param string[] $safelist regexes */
    public function __construct(private array $words, private array $safelist = [])
    {
    }

    /** Every word ([A-Za-z0-9_-]+) in the given files */
    public static function wordsIn(iterable $files): array
    {
        $words = [];
        foreach ($files as $file) {
            preg_match_all('/[A-Za-z0-9_-]+/', (string) file_get_contents($file), $m);
            foreach ($m[0] as $word) {
                $words[$word] = true;
            }
        }

        return $words;
    }

    public function purge(string $css): string
    {
        // Comments go; the licence ones (/*! ... */) are kept together at the top
        preg_match_all('#/\*!.*?\*/#s', $css, $licences);
        $css = preg_replace('#/\*.*?\*/#s', '', $css);
        $kept = $this->block($css);

        // Fonts and animations: kept only when a kept rule (or a scanned file) names them
        $used = preg_replace('/@(-webkit-|-moz-|-o-)?(keyframes|font-face)\b[^{]*\{(?:[^{}]++|\{[^{}]*+\})*+\}/', '', $kept);
        $kept = preg_replace_callback('/@font-face\{[^}]*\}/', function ($m) use ($used) {
            if (!preg_match('/font-family:\s*([\'"]?)([^;\'"}]+)\1/i', $m[0], $f)) {
                return $m[0];
            }
            return $this->named(trim($f[2]), $used) ? $m[0] : '';
        }, $kept);
        $kept = preg_replace_callback('/@(-webkit-|-moz-|-o-)?keyframes\s+([^{\s]+)\s*\{(?:[^{}]++|\{[^{}]*+\})*+\}/', function ($m) use ($used) {
            return preg_match('/animation(-name)?\s*:[^;}]*\b' . preg_quote($m[2], '/') . '\b/', $used) ? $m[0] : '';
        }, $kept);

        return implode("\n", array_unique($licences[0])) . "\n" . $kept;
    }

    private function named(string $family, string $css): bool
    {
        return stripos($css, $family) !== false || isset($this->words[$family]);
    }

    // One level of rules: plain rules are filtered, @media/@supports are filtered inside and dropped
    // when empty, @font-face/@keyframes/@page pass through (filtered afterwards)
    private function block(string $css): string
    {
        $out = '';
        $i = 0;
        $n = strlen($css);
        while ($i < $n) {
            $c = $css[$i];
            if (ctype_space($c) || $c === ';') {
                $i++;
                continue;
            }
            $open = $this->find($css, $i, '{;');
            if ($open === null) {
                break;
            }
            $head = trim(substr($css, $i, $open - $i));
            if ($css[$open] === ';') {
                // @charset / @import / stray statements
                $i = $open + 1;
                continue;
            }
            $close = $this->matching($css, $open);
            $body = substr($css, $open + 1, $close - $open - 1);
            $i = $close + 1;

            if ($head !== '' && $head[0] === '@') {
                if (preg_match('/^@(media|supports|document|-moz-document)\b/i', $head)) {
                    $inner = $this->block($body);
                    if (trim($inner) !== '') {
                        $out .= $head . '{' . $inner . '}';
                    }
                } else {
                    $out .= $head . '{' . $body . '}';
                }
                continue;
            }

            $selectors = array_filter($this->split($head), fn ($s) => $this->keeps($s));
            if ($selectors) {
                $out .= implode(',', $selectors) . '{' . $body . '}';
            }
        }

        return $out;
    }

    private function keeps(string $selector): bool
    {
        // what is inside :not(...) / [attr=...] does not have to be on the page
        $plain = preg_replace(['/\[[^\]]*\]/', '/:not\((?:[^()]*|\([^()]*\))*\)/'], '', $selector);
        preg_match_all('/[.#]((?:[A-Za-z0-9_-]|\\\\.)+)/', $plain, $m);
        foreach ($m[1] as $name) {
            $name = stripslashes($name);
            if (isset($this->words[$name])) {
                continue;
            }
            foreach ($this->safelist as $pattern) {
                if (preg_match($pattern, $name)) {
                    continue 2;
                }
            }
            return false;
        }

        return true;
    }

    // Splits a selector list at the commas that are not inside (...) or [...]
    private function split(string $head): array
    {
        $parts = [];
        $depth = 0;
        $start = 0;
        $quote = null;
        for ($i = 0, $n = strlen($head); $i < $n; $i++) {
            $c = $head[$i];
            if ($quote) {
                if ($c === '\\') {
                    $i++;
                } elseif ($c === $quote) {
                    $quote = null;
                }
            } elseif ($c === '"' || $c === "'") {
                $quote = $c;
            } elseif ($c === '\\') {
                $i++;
            } elseif ($c === '(' || $c === '[') {
                $depth++;
            } elseif ($c === ')' || $c === ']') {
                $depth--;
            } elseif ($c === ',' && $depth === 0) {
                $parts[] = trim(substr($head, $start, $i - $start));
                $start = $i + 1;
            }
        }
        $parts[] = trim(substr($head, $start));

        return array_values(array_filter($parts, fn ($p) => $p !== ''));
    }

    // First of the given characters at or after $i, outside strings and (...)
    private function find(string $css, int $i, string $chars): ?int
    {
        $quote = null;
        $paren = 0;
        for ($n = strlen($css); $i < $n; $i++) {
            $c = $css[$i];
            if ($quote) {
                if ($c === '\\') {
                    $i++;
                } elseif ($c === $quote) {
                    $quote = null;
                }
            } elseif ($c === '"' || $c === "'") {
                $quote = $c;
            } elseif ($c === '\\') {
                $i++;
            } elseif ($c === '(') {
                $paren++;
            } elseif ($c === ')') {
                $paren--;
            } elseif ($paren === 0 && str_contains($chars, $c)) {
                return $i;
            }
        }

        return null;
    }

    // The } that closes the { at $open
    private function matching(string $css, int $open): int
    {
        $depth = 0;
        $i = $open;
        $n = strlen($css);
        while ($i < $n) {
            $next = $this->find($css, $i, '{}');
            if ($next === null) {
                return $n;
            }
            $depth += $css[$next] === '{' ? 1 : -1;
            if ($depth === 0) {
                return $next;
            }
            $i = $next + 1;
        }

        return $n;
    }
}
