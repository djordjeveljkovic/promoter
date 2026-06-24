<?php
/**
 * One-off: rewrite "sub-promoter…" -> "promoter…" inside PHP single-quoted
 * string values in language files. Keys and PHP code are left untouched.
 *
 * Approach: a regex matches the array-value context — `=> '...'` or
 * `=> "..."` — and rewrites ONLY the content inside the quotes. This avoids
 * any need to round-trip through var_export (which mangles formatting).
 */

$files = glob(__DIR__ . '/lang/{en,sr}/*.php', GLOB_BRACE);

$totalChanged = 0;
$totalFiles   = 0;

// Match an array value on the right of `=>`:
//   - group 1: opening quote (single or double)
//   - group 2: content (supports escaped quotes inside)
//   - group 3: closing quote (same as opening)
// Then we rewrite group 2 only.
$valuePattern = '/(=>\s*)(\"|\')((?:\\\\.|(?!\2).)*)\2/us';

foreach ($files as $file) {
    $src = file_get_contents($file);

    $changes = 0;
    $new = preg_replace_callback($valuePattern, function ($m) use (&$changes) {
        [$full, $lhs, $quote, $value] = $m;

        // Match "sub" + optional separator + "promoter" (any case). We
        // capture the prefix ("sub"/"Sub") and the "promoter" portion
        // separately so we can derive the correct output case:
        //   "Sub-Promoter"   -> "Promoter"   (proper noun)
        //   "sub-promoter"   -> "promoter"   (common noun)
        //   "Sub-promoters"  -> "Promoters"  (sentence start, plural suffix kept)
        $rewritten = preg_replace_callback(
            '/\b(sub)[-_ ]?(promoter)/iu',
            function ($inner) {
                $subPart     = $inner[1];
                $promoterPart = $inner[2];

                // Capitalize the output if EITHER the original "sub" was
                // capital OR the original "promoter" was capital. That
                // covers both "Sub-Promoter" -> "Promoter" and
                // "Sub-promoter" -> "Promoter" (sentence-start case).
                $isCapital = (ctype_upper($subPart[0]) || ctype_upper($promoterPart[0]));
                return $isCapital ? 'Promoter' : 'promoter';
            },
            $value
        );

        if ($rewritten !== $value) {
            $changes++;
        }
        return $lhs . $quote . $rewritten . $quote;
    }, $src);

    if ($changes > 0) {
        file_put_contents($file, $new);
        $totalFiles++;
    }
    $totalChanged += $changes;

    printf("%-40s -> %d value(s) updated\n",
        str_replace(__DIR__ . '/lang/', '', $file),
        $changes
    );
}

printf("\nDone. %d value(s) rewritten across %d file(s).\n", $totalChanged, $totalFiles);