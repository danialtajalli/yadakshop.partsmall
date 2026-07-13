<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (! defined('DIDAR_API_BASE')) {
    define('DIDAR_API_BASE', 'https://app.didar.me/api/');
}

if (! function_exists('didar_contact_config')) {

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

function didar_contact_field_has_error(?string $error): bool
{
    return $error !== null && $error !== '';
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
$fieldErrors = [
    'first_name' => null,
    'last_name' => null,
    'phone' => null,
    'message' => null,
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
    } else {
        if ($values['first_name'] === '') {
            $fieldErrors['first_name'] = 'نام را وارد کنید.';
        } elseif (mb_strlen($values['first_name']) > 100) {
            $fieldErrors['first_name'] = 'نام بیش از حد طولانی است.';
        }

        if ($values['last_name'] === '') {
            $fieldErrors['last_name'] = 'نام خانوادگی را وارد کنید.';
        } elseif (mb_strlen($values['last_name']) > 100) {
            $fieldErrors['last_name'] = 'نام خانوادگی بیش از حد طولانی است.';
        }

        if ($values['phone'] === '') {
            $fieldErrors['phone'] = 'شماره موبایل را وارد کنید.';
        } elseif (! didar_contact_is_valid_mobile(didar_contact_normalize_mobile($values['phone']))) {
            $fieldErrors['phone'] = 'شماره موبایل معتبر نیست. نمونه صحیح: 09121234567';
        }

        if (mb_strlen($values['message']) > 2000) {
            $fieldErrors['message'] = 'متن پیام بیش از حد طولانی است.';
        }

        if (array_filter($fieldErrors) === []) {
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

    .didar-contact-form__field input.didar-contact-form__input--error,
    .didar-contact-form__field textarea.didar-contact-form__input--error {
        border-color: #f87171;
        background: #fffafa;
    }

    .didar-contact-form__field input.didar-contact-form__input--error:focus,
    .didar-contact-form__field textarea.didar-contact-form__input--error:focus {
        outline-color: rgb(248 113 113 / 0.25);
        border-color: #ef4444;
    }

    .didar-contact-form__error {
        margin: 0.375rem 0 0;
        font-size: 0.75rem;
        line-height: 1.5;
        color: #dc2626;
    }

    .didar-contact-form__error[hidden] {
        display: none;
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

    <form method="post" action="<?= didar_contact_e($embed ? '?embed=1' : '') ?>" novalidate data-didar-contact-form>
        <input type="hidden" name="csrf_token" value="<?= didar_contact_e($csrf) ?>">

        <div class="didar-contact-form__grid didar-contact-form__grid--two">
            <div class="didar-contact-form__field">
                <label for="first_name">نام</label>
                <input
                    id="first_name"
                    name="first_name"
                    type="text"
                    value="<?= didar_contact_e($values['first_name']) ?>"
                    maxlength="100"
                    autocomplete="given-name"
                    aria-describedby="first_name-error"
                    class="<?= didar_contact_field_has_error($fieldErrors['first_name']) ? 'didar-contact-form__input--error' : '' ?>"
                >
                <p class="didar-contact-form__error" id="first_name-error" role="alert" data-field-error="first_name"<?= didar_contact_field_has_error($fieldErrors['first_name']) ? '' : ' hidden' ?>>
                    <?= didar_contact_e($fieldErrors['first_name'] ?? '') ?>
                </p>
            </div>

            <div class="didar-contact-form__field">
                <label for="last_name">نام خانوادگی</label>
                <input
                    id="last_name"
                    name="last_name"
                    type="text"
                    value="<?= didar_contact_e($values['last_name']) ?>"
                    maxlength="100"
                    autocomplete="family-name"
                    aria-describedby="last_name-error"
                    class="<?= didar_contact_field_has_error($fieldErrors['last_name']) ? 'didar-contact-form__input--error' : '' ?>"
                >
                <p class="didar-contact-form__error" id="last_name-error" role="alert" data-field-error="last_name"<?= didar_contact_field_has_error($fieldErrors['last_name']) ? '' : ' hidden' ?>>
                    <?= didar_contact_e($fieldErrors['last_name'] ?? '') ?>
                </p>
            </div>
        </div>

        <div class="didar-contact-form__grid" style="margin-top: 1rem;">
            <div class="didar-contact-form__field">
                <label for="phone">شماره موبایل</label>
                <input
                    id="phone"
                    name="phone"
                    type="tel"
                    value="<?= didar_contact_e($values['phone']) ?>"
                    maxlength="15"
                    inputmode="tel"
                    autocomplete="tel"
                    dir="ltr"
                    placeholder="09121234567"
                    style="text-align: right;"
                    aria-describedby="phone-error"
                    class="<?= didar_contact_field_has_error($fieldErrors['phone']) ? 'didar-contact-form__input--error' : '' ?>"
                >
                <p class="didar-contact-form__error" id="phone-error" role="alert" data-field-error="phone"<?= didar_contact_field_has_error($fieldErrors['phone']) ? '' : ' hidden' ?>>
                    <?= didar_contact_e($fieldErrors['phone'] ?? '') ?>
                </p>
            </div>

            <div class="didar-contact-form__field">
                <label for="message">پیام</label>
                <textarea
                    id="message"
                    name="message"
                    maxlength="2000"
                    placeholder="پیام خود را بنویسید..."
                    aria-describedby="message-error"
                    class="<?= didar_contact_field_has_error($fieldErrors['message']) ? 'didar-contact-form__input--error' : '' ?>"
                ><?= didar_contact_e($values['message']) ?></textarea>
                <p class="didar-contact-form__error" id="message-error" role="alert" data-field-error="message"<?= didar_contact_field_has_error($fieldErrors['message']) ? '' : ' hidden' ?>>
                    <?= didar_contact_e($fieldErrors['message'] ?? '') ?>
                </p>
            </div>
        </div>

        <div style="margin-top: 1rem;">
            <button type="submit" class="didar-contact-form__submit">ارسال پیام</button>
        </div>
    </form>
</div>

<script>
(function () {
    const form = document.querySelector('[data-didar-contact-form]');
    if (!form) {
        return;
    }

    function toEnglishDigits(value) {
        const persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        const arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        const english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return value
            .replace(/[۰-۹]/g, (digit) => english[persian.indexOf(digit)])
            .replace(/[٠-٩]/g, (digit) => english[arabic.indexOf(digit)]);
    }

    function normalizeMobile(phone) {
        let value = toEnglishDigits(phone.trim()).replace(/[^\d+]/g, '');

        if (value.startsWith('+98')) {
            value = '0' + value.slice(3);
        } else if (value.startsWith('0098')) {
            value = '0' + value.slice(4);
        } else if (value.startsWith('98') && value.length === 12) {
            value = '0' + value.slice(2);
        }

        return value;
    }

    function isValidMobile(phone) {
        return /^09\d{9}$/.test(normalizeMobile(phone));
    }

    function setFieldError(name, message) {
        const input = form.querySelector('[name="' + name + '"]');
        const error = form.querySelector('[data-field-error="' + name + '"]');

        if (!input || !error) {
            return;
        }

        if (message) {
            input.classList.add('didar-contact-form__input--error');
            input.setAttribute('aria-invalid', 'true');
            error.textContent = message;
            error.hidden = false;
        } else {
            input.classList.remove('didar-contact-form__input--error');
            input.removeAttribute('aria-invalid');
            error.textContent = '';
            error.hidden = true;
        }
    }

    function validateField(name, value) {
        const trimmed = value.trim();

        if (name === 'first_name') {
            if (trimmed === '') {
                return 'نام را وارد کنید.';
            }

            if (trimmed.length > 100) {
                return 'نام بیش از حد طولانی است.';
            }

            return '';
        }

        if (name === 'last_name') {
            if (trimmed === '') {
                return 'نام خانوادگی را وارد کنید.';
            }

            if (trimmed.length > 100) {
                return 'نام خانوادگی بیش از حد طولانی است.';
            }

            return '';
        }

        if (name === 'phone') {
            if (trimmed === '') {
                return 'شماره موبایل را وارد کنید.';
            }

            if (!isValidMobile(trimmed)) {
                return 'شماره موبایل معتبر نیست. نمونه صحیح: 09121234567';
            }

            return '';
        }

        if (name === 'message' && trimmed.length > 2000) {
            return 'متن پیام بیش از حد طولانی است.';
        }

        return '';
    }

    function notifyParentHeight() {
        if (window.parent === window) {
            return;
        }

        const height = Math.max(
            document.body.scrollHeight,
            document.documentElement.scrollHeight
        );

        window.parent.postMessage({
            type: 'didar-contact-form-resize',
            height: height,
        }, window.location.origin);
    }

    form.addEventListener('submit', function (event) {
        let valid = true;

        ['first_name', 'last_name', 'phone', 'message'].forEach(function (name) {
            const input = form.querySelector('[name="' + name + '"]');
            const message = validateField(name, input ? input.value : '');
            setFieldError(name, message);

            if (message) {
                valid = false;
            }
        });

        if (!valid) {
            event.preventDefault();
        }

        notifyParentHeight();
    });

    form.querySelectorAll('input, textarea').forEach(function (input) {
        input.addEventListener('input', function () {
            setFieldError(input.name, '');
            notifyParentHeight();
        });
    });

    if (window.parent !== window) {
        notifyParentHeight();
        window.addEventListener('load', notifyParentHeight);

        if (typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(notifyParentHeight).observe(document.body);
        }
    }
})();
</script>

<?php if (! $embed) { ?>
</body>
</html>
<?php } ?>
