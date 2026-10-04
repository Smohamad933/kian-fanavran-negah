<?php

declare(strict_types=1);

/** Search Console uses a read-only OAuth scope; refresh tokens are encrypted before storage. */
function gsc_config(): array
{
    $config = app_config();
    return [
        'client_id' => trim((string) ($config['gsc_client_id'] ?? '')),
        'client_secret' => trim((string) ($config['gsc_client_secret'] ?? '')),
        'encryption_key' => trim((string) ($config['gsc_token_encryption_key'] ?? '')),
        'redirect_uri' => trim((string) ($config['gsc_redirect_uri'] ?? '')),
    ];
}

function gsc_is_configured(): bool
{
    $config = gsc_config();
    return $config['client_id'] !== ''
        && $config['client_secret'] !== ''
        && strlen($config['encryption_key']) >= 32
        && function_exists('openssl_encrypt')
        && function_exists('openssl_decrypt')
        && filter_var((string) ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN);
}

function gsc_redirect_uri(string $siteUrl): string
{
    $config = gsc_config();
    if ($config['redirect_uri'] !== '') {
        $redirectUri = filter_var($config['redirect_uri'], FILTER_VALIDATE_URL) ? $config['redirect_uri'] : '';
    } else {
        $baseUrl = seo_normalize_site_url($siteUrl);
        $redirectUri = $baseUrl !== '' ? $baseUrl . '/admin/google-callback.php' : '';
    }
    if ($redirectUri === '') return '';
    $parts = parse_url($redirectUri);
    if (!is_array($parts) || strtolower((string) ($parts['scheme'] ?? '')) !== 'https' || empty($parts['host'])) return '';
    return $redirectUri;
}

function gsc_authorization_url(string $state, string $redirectUri): string
{
    $config = gsc_config();
    $parameters = [
        'client_id' => $config['client_id'],
        'redirect_uri' => $redirectUri,
        'response_type' => 'code',
        'scope' => 'https://www.googleapis.com/auth/webmasters.readonly',
        'access_type' => 'offline',
        'include_granted_scopes' => 'true',
        'prompt' => 'consent',
        'state' => $state,
    ];
    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
}

function gsc_token_key(): string
{
    $secret = gsc_config()['encryption_key'];
    if (strlen($secret) < 32) throw new RuntimeException('کلید رمزگذاری Search Console در app/config.local.php تنظیم نشده است.');
    return hash('sha256', $secret, true);
}

function gsc_store_token(PDO $pdo, array $token): void
{
    if (!function_exists('openssl_encrypt')) throw new RuntimeException('افزونهٔ OpenSSL برای نگهداری امن اتصال گوگل فعال نیست.');
    $iv = random_bytes(12);
    $tag = '';
    $plainText = json_encode($token, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $cipherText = openssl_encrypt($plainText, 'aes-256-gcm', gsc_token_key(), OPENSSL_RAW_DATA, $iv, $tag, 'kfn-gsc-v1');
    if ($cipherText === false) throw new RuntimeException('ذخیرهٔ امن اتصال گوگل انجام نشد.');
    $encrypted = 'v1.' . base64_encode($iv . $tag . $cipherText);
    $statement = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (:setting_key, :setting_value) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    $statement->execute(['setting_key' => 'gsc_token_encrypted', 'setting_value' => $encrypted]);
}

function gsc_read_token(PDO $pdo): ?array
{
    $statement = $pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key = :setting_key LIMIT 1');
    $statement->execute(['setting_key' => 'gsc_token_encrypted']);
    $stored = (string) ($statement->fetchColumn() ?: '');
    if ($stored === '') return null;
    if (!str_starts_with($stored, 'v1.') || !function_exists('openssl_decrypt')) {
        throw new RuntimeException('اطلاعات اتصال گوگل قابل رمزگشایی نیست؛ دوباره Search Console را متصل کنید.');
    }
    $payload = base64_decode(substr($stored, 3), true);
    if ($payload === false || strlen($payload) < 29) throw new RuntimeException('اطلاعات اتصال گوگل معتبر نیست؛ دوباره Search Console را متصل کنید.');
    $iv = substr($payload, 0, 12);
    $tag = substr($payload, 12, 16);
    $cipherText = substr($payload, 28);
    $plainText = openssl_decrypt($cipherText, 'aes-256-gcm', gsc_token_key(), OPENSSL_RAW_DATA, $iv, $tag, 'kfn-gsc-v1');
    $token = $plainText !== false ? json_decode($plainText, true) : null;
    if (!is_array($token)) throw new RuntimeException('رمزگشایی اتصال گوگل ناموفق بود؛ تنظیمات کلید را بررسی و دوباره متصل کنید.');
    return $token;
}

function gsc_remove_token(PDO $pdo): void
{
    $statement = $pdo->prepare('DELETE FROM site_settings WHERE setting_key = :setting_key');
    $statement->execute(['setting_key' => 'gsc_token_encrypted']);
}

function gsc_revoke_and_remove_token(PDO $pdo): void
{
    try {
        $token = gsc_read_token($pdo);
        if (!empty($token['refresh_token'])) {
            gsc_http_json('POST', 'https://oauth2.googleapis.com/revoke', [], ['token' => (string) $token['refresh_token']], true);
        }
    } catch (Throwable $exception) {
        // Always remove local credentials even if Google is temporarily unreachable.
    }
    gsc_remove_token($pdo);
}

/** Make an HTTPS request to a Google OAuth or Search Console JSON endpoint. */
function gsc_http_json(string $method, string $url, array $headers = [], ?array $payload = null, bool $formEncoded = false): array
{
    $requestHeaders = ['Accept: application/json'];
    $content = '';
    if ($payload !== null) {
        if ($formEncoded) {
            $requestHeaders[] = 'Content-Type: application/x-www-form-urlencoded';
            $content = http_build_query($payload, '', '&', PHP_QUERY_RFC3986);
        } else {
            $requestHeaders[] = 'Content-Type: application/json';
            $content = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        }
    }
    $requestHeaders = array_merge($requestHeaders, $headers);
    $context = stream_context_create([
        'http' => [
            'method' => strtoupper($method),
            'header' => implode("\r\n", $requestHeaders),
            'content' => $content,
            'timeout' => 20,
            'ignore_errors' => true,
            'follow_location' => 0,
            'max_redirects' => 0,
            'protocol_version' => 1.1,
        ],
        'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
    ]);
    $responseText = @file_get_contents($url, false, $context);
    $statusCode = 0;
    foreach (($http_response_header ?? []) as $headerLine) {
        if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $headerLine, $matches)) $statusCode = (int) $matches[1];
    }
    $response = is_string($responseText) ? json_decode($responseText, true) : null;
    if (!is_array($response)) $response = [];
    if ($statusCode < 200 || $statusCode >= 300) {
        $message = match (true) {
            $statusCode === 400 => 'درخواست به گوگل پذیرفته نشد؛ نشانی بازگشت و تنظیمات OAuth را بررسی کنید.',
            $statusCode === 401 => 'نشست گوگل منقضی شده است؛ دوباره Search Console را متصل کنید.',
            $statusCode === 403 => 'این حساب اجازهٔ مشاهدهٔ این ویژگی را ندارد یا Search Console API فعال نیست.',
            $statusCode === 404 => 'ویژگی انتخاب‌شده در Search Console پیدا نشد.',
            $statusCode === 429 => 'محدودیت موقت درخواست‌های گوگل اعمال شده است؛ کمی بعد دوباره تلاش کنید.',
            $statusCode >= 500 => 'سرویس گوگل موقتاً پاسخ‌گو نیست؛ کمی بعد دوباره تلاش کنید.',
            default => 'ارتباط امن با گوگل برقرار نشد؛ دسترسی خروجی HTTPS سرور را بررسی کنید.',
        };
        throw new RuntimeException($message);
    }
    return $response;
}

function gsc_exchange_code(PDO $pdo, string $code, string $redirectUri): array
{
    $config = gsc_config();
    $token = gsc_http_json('POST', 'https://oauth2.googleapis.com/token', [], [
        'code' => $code,
        'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'redirect_uri' => $redirectUri,
        'grant_type' => 'authorization_code',
    ], true);
    if (empty($token['access_token'])) throw new RuntimeException('گوگل توکن دسترسی برنگرداند؛ تنظیمات OAuth را بررسی کنید.');
    $previousToken = gsc_read_token($pdo);
    if (empty($token['refresh_token']) && !empty($previousToken['refresh_token'])) $token['refresh_token'] = $previousToken['refresh_token'];
    if (empty($token['refresh_token'])) throw new RuntimeException('توکن ماندگار از گوگل دریافت نشد؛ دسترسی برنامه را از حساب Google لغو کنید و دوباره متصل شوید.');
    $token['expires_at'] = time() + max(0, (int) ($token['expires_in'] ?? 3600));
    gsc_store_token($pdo, $token);
    return $token;
}

function gsc_refresh_access_token(PDO $pdo, bool $force = false): string
{
    $token = gsc_read_token($pdo);
    if ($token === null) throw new RuntimeException('ابتدا حساب Google Search Console را متصل کنید.');
    if (!$force && !empty($token['access_token']) && (int) ($token['expires_at'] ?? 0) > time() + 90) return (string) $token['access_token'];
    if (empty($token['refresh_token'])) throw new RuntimeException('اتصال گوگل منقضی شده است؛ دوباره متصل شوید.');
    $config = gsc_config();
    $refreshed = gsc_http_json('POST', 'https://oauth2.googleapis.com/token', [], [
        'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'refresh_token' => $token['refresh_token'],
        'grant_type' => 'refresh_token',
    ], true);
    if (empty($refreshed['access_token'])) throw new RuntimeException('گوگل توکن تازه صادر نکرد؛ دوباره Search Console را متصل کنید.');
    $token = array_merge($token, $refreshed);
    $token['expires_at'] = time() + max(0, (int) ($refreshed['expires_in'] ?? 3600));
    gsc_store_token($pdo, $token);
    return (string) $token['access_token'];
}

function gsc_api_request(PDO $pdo, string $method, string $url, ?array $payload = null): array
{
    $accessToken = gsc_refresh_access_token($pdo);
    try {
        return gsc_http_json($method, $url, ['Authorization: Bearer ' . $accessToken], $payload);
    } catch (RuntimeException $exception) {
        if (!str_contains($exception->getMessage(), 'نشست گوگل منقضی')) throw $exception;
        $accessToken = gsc_refresh_access_token($pdo, true);
        return gsc_http_json($method, $url, ['Authorization: Bearer ' . $accessToken], $payload);
    }
}

function gsc_list_sites(PDO $pdo): array
{
    $response = gsc_api_request($pdo, 'GET', 'https://www.googleapis.com/webmasters/v3/sites');
    $sites = $response['siteEntry'] ?? [];
    return is_array($sites) ? $sites : [];
}

function gsc_search_analytics(PDO $pdo, string $propertyUrl, string $startDate, string $endDate): array
{
    $url = 'https://www.googleapis.com/webmasters/v3/sites/' . rawurlencode($propertyUrl) . '/searchAnalytics/query';
    $response = gsc_api_request($pdo, 'POST', $url, [
        'startDate' => $startDate,
        'endDate' => $endDate,
        'dimensions' => ['query', 'page'],
        'rowLimit' => 100,
        'type' => 'web',
    ]);
    $rows = $response['rows'] ?? [];
    return is_array($rows) ? $rows : [];
}

function gsc_setting(PDO $pdo, string $key, string $value): void
{
    $statement = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (:setting_key, :setting_value) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    $statement->execute(['setting_key' => $key, 'setting_value' => $value]);
}

function gsc_read_setting(PDO $pdo, string $key): string
{
    $statement = $pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key = :setting_key LIMIT 1');
    $statement->execute(['setting_key' => $key]);
    return (string) ($statement->fetchColumn() ?: '');
}
