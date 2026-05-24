<?php

$head = implode('', array_slice(file(__DIR__.'/../docs/LAPORAN_PENGUJIAN_HCD_DAN_BLACKBOX.md'), 0, 199));
$tail = file_get_contents(__DIR__.'/../docs/_blackbox_tables_snippet.md');
file_put_contents(__DIR__.'/../docs/LAPORAN_PENGUJIAN_HCD_DAN_BLACKBOX.md', $head.$tail);
@unlink(__DIR__.'/../docs/_blackbox_tables_snippet.md');
echo "Laporan diperbaiki.\n";
