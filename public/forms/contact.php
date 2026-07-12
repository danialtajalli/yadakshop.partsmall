<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (! defined('DIDAR_API_BASE')) {
    define('DIDAR_API_BASE', 'https://app.didar.me/api/');
}

/**
 * Self-contained module configuration — edit values here per deployment.
 *
 * @return array{
 *     api_key: string,
 *     owner_username: string,
 *     deal_field_key: string,
 *     verify_ssl: bool,
 *     curl_cafile: ?string
 * }
 */
function didar_contact_config(): array
{
    return [
        'api_key' => 'h39aw1spekoalxq1irhyntgqi7h2k8dy',
        'owner_username' => 'eamirghaed@gmail.com',
        'deal_field_key' => 'Field_8783_0_1',
        'verify_ssl' => true,
        'curl_cafile' => 'C:/wamp64/bin/php/php8.3.28/extras/ssl/cacert.pem',
    ];
}

function didar_contact_csrf_token(): string
{
    if (empty($_SESSION['didar_contact_csrf'])) {
        $_SESSION['didar_contact_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['didar_contact_csrf'];
}

function didar_contact_verify_csrf(string $token): bool
{
    return hash_equals(didar_contact_csrf_token(), $token);
}

function didar_resolve_ca_file(): ?string
{
    $candidates = array_filter([
        didar_contact_config()['curl_cafile'],
        ini_get('curl.cainfo') ?: null,
        ini_get('openssl.cafile') ?: null,
        dirname(PHP_BINARY).DIRECTORY_SEPARATOR.'extras'.DIRECTORY_SEPARATOR.'ssl'.DIRECTORY_SEPARATOR.'cacert.pem',
        'C:/wamp64/bin/php/'.PHP_VERSION.'/extras/ssl/cacert.pem',
        'C:/wamp64/bin/php/php'.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION.'/extras/ssl/cacert.pem',
    ]);

    foreach ($candidates as $candidate) {
        $path = str_replace('\\', '/', (string) $candidate);

        if ($path !== '' && is_readable($path)) {
            return $path;
        }
    }

    return null;
}

/**
 * @return array<int, bool|int|string>
 */
function didar_curl_ssl_options(): array
{
    $caFile = didar_resolve_ca_file();

    if ($caFile !== null) {
        return [
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO => $caFile,
        ];
    }

    $verifySsl = didar_contact_config()['verify_ssl'];

    return [
        CURLOPT_SSL_VERIFYPEER => $verifySsl,
        CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
    ];
    }

/**
 * @return array{ok: bool, status: int, data: mixed, error: ?string}
 */
function didar_post(string $endpoint, array|object $body, string $apiKey): array
{
    $url = DIDAR_API_BASE.ltrim($endpoint, '/').'?apikey='.urlencode($apiKey);

    $ch = curl_init($url);

    if ($ch === false) {
        return ['ok' => false, 'status' => 0, 'data' => null, 'error' => 'curl_init_failed'];
    }

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 30,
    ] + didar_curl_ssl_options());

    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        return ['ok' => false, 'status' => $status, 'data' => null, 'error' => $curlError ?: 'curl_exec_failed'];
    }

    $decoded = json_decode($raw, true);

    if (! is_array($decoded)) {
        return ['ok' => false, 'status' => $status, 'data' => null, 'error' => 'invalid_json'];
    }

    if ($status < 200 || $status >= 300) {
        return ['ok' => false, 'status' => $status, 'data' => $decoded, 'error' => 'http_'.$status];
    }

    return ['ok' => true, 'status' => $status, 'data' => $decoded, 'error' => null];
}

/**
 * @param  array<string, mixed>  $response
 */
function extract_person_id(array $response): ?string
{
    $payload = $response['Response'] ?? null;

    if (! is_array($payload)) {
        return null;
    }

    foreach (['PersonId', 'Id', 'ContactId'] as $key) {
        if (! empty($payload[$key]) && is_string($payload[$key])) {
            return $payload[$key];
        }
    }

    if (isset($payload['Contact']) && is_array($payload['Contact']) && ! empty($payload['Contact']['Id'])) {
        return (string) $payload['Contact']['Id'];
    }

    if (isset($payload['Person']) && is_array($payload['Person']) && ! empty($payload['Person']['Id'])) {
        return (string) $payload['Person']['Id'];
    }

    return null;
}

/**
 * @param  array<string, mixed>  $response
 */
function extract_first_product_id(array $response): ?string
{
    $products = $response['Response'] ?? null;

    if (! is_array($products) || $products === []) {
        return null;
    }

    $first = $products[0] ?? null;

    return is_array($first) && ! empty($first['Id']) ? (string) $first['Id'] : null;
}

/**
 * @param  array<string, mixed>  $response
 */
function extract_owner_user_id(array $response, string $username): ?string
{
    $users = $response['Response'] ?? null;

    if (! is_array($users)) {
        return null;
    }

    foreach ($users as $user) {
        if (! is_array($user)) {
            continue;
        }

        if (($user['UserName'] ?? null) === $username && ! empty($user['UserId'])) {
            return (string) $user['UserId'];
        }
    }

    return null;
}

/**
 * @return array{pipeline_stage_id: ?string, pipeline_id: ?string}
 * @param  array<string, mixed>  $response
 */
function extract_first_pipeline_stage(array $response): array
{
    $pipelines = $response['Response'] ?? null;

    if (! is_array($pipelines) || $pipelines === []) {
        return ['pipeline_stage_id' => null, 'pipeline_id' => null];
    }

    $pipeline = $pipelines[0];

    if (! is_array($pipeline)) {
        return ['pipeline_stage_id' => null, 'pipeline_id' => null];
    }

    $stages = $pipeline['Stages'] ?? [];
    $firstStage = is_array($stages) && $stages !== [] ? $stages[0] : null;

    return [
        'pipeline_stage_id' => is_array($firstStage) && ! empty($firstStage['Id']) ? (string) $firstStage['Id'] : null,
        'pipeline_id' => ! empty($pipeline['Id']) ? (string) $pipeline['Id'] : null,
    ];
}

/**
 * @return array{success: bool, message: string}
 */
function submit_contact_lead(
    string $apiKey,
    string $ownerUsername,
    string $dealFieldKey,
    string $firstName,
    string $lastName,
    string $phone,
    string $message,
): array {
    $productResponse = didar_post('product/search', [
        'Criteria' => new stdClass(),
        'From' => 0,
        'Limit' => 10,
    ], $apiKey);

    if (! $productResponse['ok']) {
        error_log('Didar product/search failed: '.($productResponse['error'] ?? 'unknown'));

        return ['success' => false, 'message' => 'ارسال پیام با خطا مواجه شد. لطفاً بعداً دوباره تلاش کنید.'];
    }

    $productId = extract_first_product_id($productResponse['data']);

    if ($productId === null) {
        error_log('Didar product/search returned no products.');

        return ['success' => false, 'message' => 'ارسال پیام با خطا مواجه شد. لطفاً بعداً دوباره تلاش کنید.'];
    }

    $contactResponse = didar_post('contact/save', [
        'Contact' => [
            'VisibilityType' => 'OwnerGroup',
            'FirstName' => $firstName,
            'LastName' => $lastName,
            'WorkPhone' => $phone,
            'Fields' => new stdClass(),
            'Type' => 'Person',
        ],
    ], $apiKey);

    if (! $contactResponse['ok']) {
        error_log('Didar contact/save failed: '.($contactResponse['error'] ?? 'unknown'));

        return ['success' => false, 'message' => 'ارسال پیام با خطا مواجه شد. لطفاً بعداً دوباره تلاش کنید.'];
    }

    $personId = extract_person_id($contactResponse['data']);

    if ($personId === null) {
        error_log('Didar contact/save returned no person id.');

        return ['success' => false, 'message' => 'ارسال پیام با خطا مواجه شد. لطفاً بعداً دوباره تلاش کنید.'];
    }

    $usersResponse = didar_post('User/List', new stdClass(), $apiKey);

    if (! $usersResponse['ok']) {
        error_log('Didar User/List failed: '.($usersResponse['error'] ?? 'unknown'));

        return ['success' => false, 'message' => 'ارسال پیام با خطا مواجه شد. لطفاً بعداً دوباره تلاش کنید.'];
    }

    $ownerId = extract_owner_user_id($usersResponse['data'], $ownerUsername);

    if ($ownerId === null) {
        error_log('Didar User/List did not contain owner username: '.$ownerUsername);

        return ['success' => false, 'message' => 'ارسال پیام با خطا مواجه شد. لطفاً بعداً دوباره تلاش کنید.'];
    }

    $pipelineResponse = didar_post('pipeline/list/0', new stdClass(), $apiKey);

    if (! $pipelineResponse['ok']) {
        error_log('Didar pipeline/list/0 failed: '.($pipelineResponse['error'] ?? 'unknown'));

        return ['success' => false, 'message' => 'ارسال پیام با خطا مواجه شد. لطفاً بعداً دوباره تلاش کنید.'];
    }

    $pipeline = extract_first_pipeline_stage($pipelineResponse['data']);

    if ($pipeline['pipeline_stage_id'] === null) {
        error_log('Didar pipeline/list/0 returned no stage id.');

        return ['success' => false, 'message' => 'ارسال پیام با خطا مواجه شد. لطفاً بعداً دوباره تلاش کنید.'];
    }

    $dealTitle = 'معامله تماس — '.trim($firstName.' '.$lastName);
    $dealDescription = $message !== '' ? $message : 'درخواست تماس از وب‌سایت';

    $dealResponse = didar_post('deal/save', [
        'Deal' => [
            'PersonId' => $personId,
            'Title' => $dealTitle,
            'OwnerId' => $ownerId,
            'PipelineStageId' => $pipeline['pipeline_stage_id'],
            'SegmentIds' => [],
            'Description' => $dealDescription,
            'VisibilityType' => 'All',
            'Fields' => [
                $dealFieldKey => $message !== '' ? $message : $dealDescription,
            ],
        ],
        'DealItems' => [
            [
                'Quantity' => 1,
                'ProductId' => $productId,
            ],
        ],
        'IsWon' => false,
        'WelcomeQuoteId' => null,
    ], $apiKey);

    if (! $dealResponse['ok']) {
        error_log('Didar deal/save failed: '.($dealResponse['error'] ?? 'unknown'));

        return ['success' => false, 'message' => 'ارسال پیام با خطا مواجه شد. لطفاً بعداً دوباره تلاش کنید.'];
    }

    return ['success' => true, 'message' => 'پیام شما با موفقیت ثبت شد. به زودی با شما تماس خواهیم گرفت.'];
}

function didar_contact_e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function didar_contact_to_english_digits(string $value): string
{
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    return str_replace($persian, $english, str_replace($arabic, $english, $value));
}

function didar_contact_normalize_mobile(string $phone): string
{
    $phone = didar_contact_to_english_digits(trim($phone));
    $phone = preg_replace('/[^\d+]/', '', $phone) ?? '';

    if (str_starts_with($phone, '+98')) {
        $phone = '0'.substr($phone, 3);
    } elseif (str_starts_with($phone, '0098')) {
        $phone = '0'.substr($phone, 4);
    } elseif (str_starts_with($phone, '98') && strlen($phone) === 12) {
        $phone = '0'.substr($phone, 2);
    }

    return $phone;
}

function didar_contact_is_valid_mobile(string $phone): bool
{
    return (bool) preg_match('/^09\d{9}$/', $phone);
}

$config = didar_contact_config();
$apiKey = $config['api_key'];
$ownerUsername = $config['owner_username'];
$dealFieldKey = $config['deal_field_key'];
$embed = isset($_GET['embed']) && $_GET['embed'] === '1';

$values = [
    'first_name' => '',
    'last_name' => '',
    'phone' => '',
    'message' => '',
];
$statusMessage = null;
$statusType = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['first_name'] = trim((string) ($_POST['first_name'] ?? ''));
    $values['last_name'] = trim((string) ($_POST['last_name'] ?? ''));
    $values['phone'] = trim((string) ($_POST['phone'] ?? ''));
    $values['message'] = trim((string) ($_POST['message'] ?? ''));

    if (! didar_contact_verify_csrf((string) ($_POST['csrf_token'] ?? ''))) {
        $statusType = 'error';
        $statusMessage = 'نشست شما منقضی شده است. لطفاً دوباره تلاش کنید.';
    } elseif ($apiKey === null || $apiKey === '') {
        $statusType = 'error';
        $statusMessage = 'پیکربندی فرم تماس کامل نیست.';
    } elseif ($values['first_name'] === '' || $values['last_name'] === '' || $values['phone'] === '') {
        $statusType = 'error';
        $statusMessage = 'لطفاً نام، نام خانوادگی و شماره موبایل را وارد کنید.';
    } elseif (mb_strlen($values['first_name']) > 100 || mb_strlen($values['last_name']) > 100) {
        $statusType = 'error';
        $statusMessage = 'نام یا نام خانوادگی بیش از حد طولانی است.';
    } elseif (! didar_contact_is_valid_mobile(didar_contact_normalize_mobile($values['phone']))) {
        $statusType = 'error';
        $statusMessage = 'شماره موبایل معتبر نیست. نمونه صحیح: 09121234567';
    } elseif (mb_strlen($values['message']) > 2000) {
        $statusType = 'error';
        $statusMessage = 'متن پیام بیش از حد طولانی است.';
    } else {
        $values['phone'] = didar_contact_normalize_mobile($values['phone']);

        $result = submit_contact_lead(
            $apiKey,
            $ownerUsername,
            $dealFieldKey,
            $values['first_name'],
            $values['last_name'],
            $values['phone'],
            $values['message'],
        );

        $statusType = $result['success'] ? 'success' : 'error';
        $statusMessage = $result['message'];

        if ($result['success']) {
            $values = [
                'first_name' => '',
                'last_name' => '',
                'phone' => '',
                'message' => '',
            ];
        }
    }
}

$csrf = didar_contact_csrf_token();

if (! $embed) {
    ?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>فرم تماس</title>
</head>
<body>
    <?php
}

?>
<style>
    .didar-contact-form {
        font-family: Tahoma, Arial, sans-serif;
        color: #0f172a;
        direction: rtl;
    }

    .didar-contact-form * {
        box-sizing: border-box;
    }

    .didar-contact-form__alert {
        margin-bottom: 1rem;
        border-radius: 0.75rem;
        padding: 0.875rem 1rem;
        font-size: 0.875rem;
        line-height: 1.6;
    }

    .didar-contact-form__alert--success {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .didar-contact-form__alert--error {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .didar-contact-form__grid {
        display: grid;
        gap: 1rem;
    }

    @media (min-width: 640px) {
        .didar-contact-form__grid--two {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .didar-contact-form__field label {
        display: block;
        margin-bottom: 0.375rem;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .didar-contact-form__field input,
    .didar-contact-form__field textarea {
        width: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 0.75rem 0.875rem;
        font: inherit;
        background: #fff;
    }

    .didar-contact-form__field textarea {
        min-height: 8rem;
        resize: vertical;
    }

    .didar-contact-form__field input:focus,
    .didar-contact-form__field textarea:focus {
        outline: 2px solid rgb(63 72 87 / 0.25);
        border-color: #3f4857;
    }

    .didar-contact-form__submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        border: 0;
        border-radius: 0.75rem;
        padding: 0.875rem 1rem;
        background: #3f4857;
        color: #fff;
        font: inherit;
        font-weight: 600;
        cursor: pointer;
    }

    .didar-contact-form__submit:hover {
        background: #222c3d;
    }
</style>

<div class="didar-contact-form">
    <?php if ($statusMessage !== null) { ?>
        <div class="didar-contact-form__alert didar-contact-form__alert--<?= didar_contact_e($statusType ?? 'error') ?>">
            <?= didar_contact_e($statusMessage) ?>
        </div>
    <?php } ?>

    <form method="post" action="<?= didar_contact_e($embed ? '?embed=1' : '') ?>">
        <input type="hidden" name="csrf_token" value="<?= didar_contact_e($csrf) ?>">

        <div class="didar-contact-form__grid didar-contact-form__grid--two">
            <div class="didar-contact-form__field">
                <label for="first_name">نام</label>
                <input id="first_name" name="first_name" type="text" value="<?= didar_contact_e($values['first_name']) ?>" required maxlength="100" autocomplete="given-name">
            </div>

            <div class="didar-contact-form__field">
                <label for="last_name">نام خانوادگی</label>
                <input id="last_name" name="last_name" type="text" value="<?= didar_contact_e($values['last_name']) ?>" required maxlength="100" autocomplete="family-name">
            </div>
        </div>

        <div class="didar-contact-form__grid" style="margin-top: 1rem;">
            <div class="didar-contact-form__field">
                <label for="phone">شماره موبایل</label>
                <input id="phone" name="phone" type="tel" value="<?= didar_contact_e($values['phone']) ?>" required maxlength="15" inputmode="tel" autocomplete="tel" dir="ltr" placeholder="09121234567" pattern="09[0-9]{9}" title="شماره موبایل باید با 09 شروع شود و 11 رقم باشد" style="text-align: right;">
            </div>

            <div class="didar-contact-form__field">
                <label for="message">پیام</label>
                <textarea id="message" name="message" maxlength="2000" placeholder="پیام خود را بنویسید..."><?= didar_contact_e($values['message']) ?></textarea>
            </div>
        </div>

        <div style="margin-top: 1rem;">
            <button type="submit" class="didar-contact-form__submit">ارسال پیام</button>
        </div>
    </form>
</div>

<?php if (! $embed) { ?>
</body>
</html>
<?php } ?>
