<?php
// Jednorazowo: przenosi portal z /nowy na glowny adres, stara strone chowa do public_html_stare. Usuwa sie sam.
header('Content-Type: text/plain; charset=utf-8');
$pub = __DIR__;                                   // .../domains/kanal19.pl/public_html
$backup = dirname(__DIR__).'/public_html_stare';
$app = dirname(__DIR__).'/kanal19';
$me = basename(__FILE__);

if (!is_dir("$pub/nowy") || !file_exists("$pub/nowy/index.php")) exit("BLAD: brak folderu nowy\n");
if (is_dir($backup)) exit("BLAD: public_html_stare juz istnieje\n");

mkdir($backup, 0755);
foreach (scandir($pub) as $f) {
    if (in_array($f, ['.', '..', 'nowy', $me], true)) continue;
    rename("$pub/$f", "$backup/$f");
}
foreach (scandir("$pub/nowy") as $f) {
    if ($f === '.' || $f === '..') continue;
    rename("$pub/nowy/$f", "$pub/$f");
}
rmdir("$pub/nowy");

$idx = file_get_contents("$pub/index.php");
file_put_contents("$pub/index.php", str_replace("__DIR__.'/../../kanal19'", "__DIR__.'/../kanal19'", $idx));

$env = file_get_contents("$app/.env");
file_put_contents("$app/.env", preg_replace('/^APP_URL=.*/m', 'APP_URL=https://kanal19.pl', $env));
@unlink("$app/storage/HASLO-ADMINA.txt.tmp");
$h = "$app/storage/HASLO-ADMINA.txt";
if (file_exists($h)) file_put_contents($h, str_replace('kanal19.pl/nowy/', 'kanal19.pl/', file_get_contents($h)));
foreach (glob("$app/storage/framework/views/*.php") ?: [] as $v) unlink($v);

unlink(__FILE__);
echo "OK przeniesiono. Stara strona: public_html_stare\n";
