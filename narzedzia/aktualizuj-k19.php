<?php
// Jednorazowa aktualizacja portalu z galezi "deploy" na GitHubie. Nie rusza .env, bazy ani storage. Usuwa sie sam.
@set_time_limit(300);
@ini_set('memory_limit', '512M');
header('Content-Type: text/plain; charset=utf-8');

$pub = __DIR__;                                  // .../domains/kanal19.pl/public_html
$app = dirname(__DIR__).'/kanal19';
$tmp = dirname(__DIR__).'/_k19tmp';
$url = 'https://codeload.github.com/lukoglobalcom-bot/kanal19/zip/refs/heads/deploy';

// Ochrona zmian zrobionych recznie na serwerze: zapamietujemy sumy wgranych plikow.
// Jesli plik na serwerze rozni sie od tego, co wgralismy ostatnio, przed nadpisaniem robimy kopie.
$GLOBALS['k19hashFile'] = dirname(__DIR__).'/kanal19/storage/k19-wgrane-pliki.json';
$GLOBALS['k19old'] = is_file($GLOBALS['k19hashFile']) ? (json_decode(file_get_contents($GLOBALS['k19hashFile']), true) ?: []) : null;
$GLOBALS['k19new'] = [];
$GLOBALS['k19kopie'] = [];
function rcopy($src, $dst) {
    if (is_dir($src)) {
        if (!is_dir($dst)) mkdir($dst, 0755, true);
        foreach (scandir($src) as $f) if ($f !== '.' && $f !== '..') rcopy("$src/$f", "$dst/$f");
    } else {
        $newHash = md5_file($src);
        if (is_file($dst) && $GLOBALS['k19old'] !== null && strpos($dst, '/vendor/') === false) {
            $cur = md5_file($dst);
            $known = $GLOBALS['k19old'][$dst] ?? null;
            if ($cur !== $newHash && $cur !== $known) {
                $copy = $dst.'.przed-aktualizacja-'.date('Ymd-His');
                copy($dst, $copy);
                $GLOBALS['k19kopie'][] = $copy;
            }
        }
        copy($src, $dst);
        if (strpos($dst, '/vendor/') === false) $GLOBALS['k19new'][$dst] = $newHash;
    }
}
function rdel($p) {
    if (is_dir($p) && !is_link($p)) { foreach (scandir($p) as $f) if ($f !== '.' && $f !== '..') rdel("$p/$f"); rmdir($p); }
    elseif (file_exists($p)) unlink($p);
}

rdel($tmp);
mkdir($tmp, 0755, true);
$in = fopen($url, 'rb', false, stream_context_create(['http' => ['timeout' => 240, 'user_agent' => 'kanal19-updater']]));
if (!$in) exit("BLAD: nie mozna pobrac\n");
$out = fopen("$tmp/deploy.zip", 'wb');
stream_copy_to_stream($in, $out);
fclose($in); fclose($out);

$z = new ZipArchive();
if ($z->open("$tmp/deploy.zip") !== true) exit("BLAD: zly zip\n");
$z->extractTo("$tmp/x");
$root = "$tmp/x/".rtrim($z->getNameIndex(0), '/');
$z->close();

// Kod aplikacji (bez .env, bazy, storage i vendor)
foreach (['app', 'bootstrap/app.php', 'bootstrap/providers.php', 'config', 'database/migrations', 'database/seeders', 'lang', 'resources', 'routes', '.env.example', 'composer.json', 'composer.lock'] as $p) {
    if (file_exists("$root/kanal19/$p")) rcopy("$root/kanal19/$p", "$app/$p");
}
// Biblioteki tylko gdy zmienil sie composer.lock
$lockNew = @md5_file("$root/kanal19/composer.lock");
if ($lockNew && $lockNew !== @md5_file("$app/vendor/.k19lock")) {
    rcopy("$root/kanal19/vendor", "$app/vendor");
    file_put_contents("$app/vendor/.k19lock", file_get_contents("$root/kanal19/composer.lock"));
}
// Pliki publiczne (bez index.php, ktory ma tu inna sciezke)
foreach (scandir("$root/public_html/nowy") as $f) {
    if (in_array($f, ['.', '..', 'index.php'], true)) continue;
    rcopy("$root/public_html/nowy/$f", "$pub/$f");
}
// Nowe klucze w .env (puste), jesli ich brak
$env = file_get_contents("$app/.env");
foreach (['SMS_API_TOKEN', 'SMS_SENDER', 'ROUTING_URL', 'ROUTING_KEY', 'LIVEKIT_URL', 'LIVEKIT_KEY', 'LIVEKIT_SECRET'] as $k) {
    if (!preg_match("/^$k=/m", $env)) $env = rtrim($env)."\n$k=\n";
}
file_put_contents("$app/.env", $env);

foreach (glob("$app/storage/framework/views/*.php") ?: [] as $v) unlink($v);
foreach (glob("$app/bootstrap/cache/*.php") ?: [] as $c) if (basename($c) !== 'packages.php' && basename($c) !== 'services.php') unlink($c);

require "$app/vendor/autoload.php";
$laravel = require "$app/bootstrap/app.php";
$laravel->usePublicPath($pub);
$kernel = $laravel->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$kernel->call('migrate', ['--force' => true]);
echo $kernel->output();

file_put_contents($GLOBALS['k19hashFile'], json_encode(array_merge($GLOBALS['k19old'] ?? [], $GLOBALS['k19new'])));
foreach ($GLOBALS['k19kopie'] as $c) echo "UWAGA: plik byl zmieniony na serwerze – zachowano kopie: $c\n";

rdel($tmp);
unlink(__FILE__);
echo "OK zaktualizowano\n";
