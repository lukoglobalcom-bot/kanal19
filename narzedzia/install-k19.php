<?php
// Jednorazowy instalator portalu kanal19.pl. Usuwa się sam po zakończeniu.
@set_time_limit(300);
@ini_set('memory_limit', '512M');
header('Content-Type: text/plain; charset=utf-8');

$domain = dirname(__DIR__, 2);                 // .../domains/kanal19.pl
$app = $domain.'/kanal19';
$pub = $domain.'/public_html/nowy';
$tmp = $domain.'/_k19tmp';
$zip = $tmp.'/deploy.zip';
$url = 'https://codeload.github.com/lukoglobalcom-bot/kanal19/zip/refs/heads/deploy';
$step = $_GET['krok'] ?? '';

function rcopy($src, $dst) {
    if (is_dir($src)) {
        if (!is_dir($dst)) mkdir($dst, 0755, true);
        foreach (scandir($src) as $f) if ($f !== '.' && $f !== '..') rcopy("$src/$f", "$dst/$f");
    } else {
        copy($src, $dst);
    }
}
function rdel($p) {
    if (is_dir($p) && !is_link($p)) { foreach (scandir($p) as $f) if ($f !== '.' && $f !== '..') rdel("$p/$f"); rmdir($p); }
    elseif (file_exists($p)) unlink($p);
}

if ($step === 'pobierz') {
    if (!is_dir($tmp)) mkdir($tmp, 0755, true);
    $ctx = stream_context_create(['http' => ['timeout' => 240, 'user_agent' => 'kanal19-installer']]);
    $in = fopen($url, 'rb', false, $ctx);
    if (!$in) exit("BLAD: nie mozna pobrac\n");
    $out = fopen($zip, 'wb');
    $n = stream_copy_to_stream($in, $out);
    fclose($in); fclose($out);
    exit("OK pobrano $n bajtow\n");
}

if ($step === 'rozpakuj') {
    $z = new ZipArchive();
    if ($z->open($zip) !== true) exit("BLAD: zly plik zip\n");
    $z->extractTo($tmp.'/x');
    $root = $tmp.'/x/'.rtrim($z->getNameIndex(0), '/');
    $z->close();
    if (!is_dir("$root/kanal19/vendor")) exit("BLAD: brak vendor w paczce\n");
    if (is_dir($app)) exit("BLAD: folder kanal19 juz istnieje\n");
    rename("$root/kanal19", $app);
    rcopy("$root/public_html/nowy", $pub);
    exit("OK rozpakowano\n");
}

if ($step === 'konfiguruj') {
    if (!file_exists("$app/.env")) {
        $env = file_get_contents("$app/.env.example");
        $env = preg_replace(
            ['/^APP_ENV=.*/m', '/^APP_DEBUG=.*/m', '/^APP_URL=.*/m', '/^APP_KEY=.*/m', '/^LOG_LEVEL=.*/m'],
            ['APP_ENV=production', 'APP_DEBUG=false', 'APP_URL=https://kanal19.pl/nowy', 'APP_KEY=base64:'.base64_encode(random_bytes(32)), 'LOG_LEVEL=info'],
            $env
        );
        file_put_contents("$app/.env", $env);
        chmod("$app/.env", 0600);
    }
    if (!file_exists("$app/database/database.sqlite")) touch("$app/database/database.sqlite");
    foreach (['storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'bootstrap/cache'] as $d) {
        if (!is_dir("$app/$d")) mkdir("$app/$d", 0755, true);
    }

    require "$app/vendor/autoload.php";
    $laravel = require "$app/bootstrap/app.php";
    $laravel->usePublicPath($pub);
    $kernel = $laravel->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    $kernel->call('migrate', ['--force' => true]);
    echo $kernel->output();

    // Konto administratora – haslo zapisane tylko w pliku na serwerze (poza public_html)
    $email = 'admin@kanal19.pl';
    if (!App\Models\User::where('email', $email)->exists()) {
        $pass = substr(str_replace(['/', '+', '='], '', base64_encode(random_bytes(18))), 0, 16);
        $u = App\Models\User::create(['name' => 'Administrator', 'email' => $email, 'password' => $pass, 'locale' => 'pl']);
        $u->forceFill(['role' => 'admin', 'phone_verified_at' => now()])->save();
        file_put_contents("$app/storage/HASLO-ADMINA.txt", "Logowanie: https://kanal19.pl/nowy/logowanie\nE-mail: $email\nHaslo: $pass\n\nPo zapisaniu hasla usun ten plik.\n");
        chmod("$app/storage/HASLO-ADMINA.txt", 0600);
        echo "OK admin utworzony\n";
    }
    echo "OK skonfigurowano\n";
    exit;
}

if ($step === 'sprzataj') {
    rdel($tmp);
    unlink(__FILE__);
    exit("OK posprzatano, instalator usuniety\n");
}

echo "PHP ".PHP_VERSION."\nkroki: pobierz, rozpakuj, konfiguruj, sprzataj\n";
