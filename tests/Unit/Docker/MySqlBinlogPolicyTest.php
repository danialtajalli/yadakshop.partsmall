<?php

namespace Tests\Unit\Docker;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class MySqlBinlogPolicyTest extends TestCase
{
    public function test_mutations_refuse_missing_backup_and_archive_or_replication(): void
    {
        foreach ([
            ['apply', [], 'BACKUP_VERIFIED'],
            ['apply', ['BACKUP_VERIFIED' => '1'], 'RESTORE_VERIFIED'],
            ['apply', ['BACKUP_VERIFIED' => '1', 'RESTORE_VERIFIED' => '1'], 'RECOVERY_REVIEWED'],
            ['purge', $this->verifiedFlags(), 'Verify every binlog'],
            ['apply', $this->verifiedFlags() + ['MOCK_REPLICAS' => '1'], ''],
            ['apply', $this->verifiedFlags() + ['MOCK_PROJECT' => 'other-site'], ''],
            ['apply', $this->verifiedFlags() + ['MYSQL_BINLOG_EXPIRE_LOGS_SECONDS' => '0'], ''],
        ] as [$mode, $environment, $message]) {
            [$process, $mutations] = $this->runPolicy($mode, $environment);
            $this->assertFalse($process->isSuccessful());
            $this->assertSame('', $mutations);
            if ($message !== '') {
                $this->assertStringContainsString($message, $process->getErrorOutput());
            }
        }
    }

    public function test_verified_apply_and_purge_issue_only_expected_mutations(): void
    {
        [$process, $mutations] = $this->runPolicy('apply', $this->verifiedFlags());
        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());
        $this->assertStringContainsString('SET PERSIST max_binlog_size=67108864;', $mutations);
        $this->assertStringContainsString('SET PERSIST binlog_transaction_compression=ON;', $mutations);
        $this->assertStringContainsString('SET PERSIST binlog_expire_logs_seconds=86400;', $mutations);

        [$process, $mutations] = $this->runPolicy('purge', $this->verifiedFlags() + ['BINLOG_ARCHIVE_VERIFIED' => '1']);
        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());
        $this->assertSame("PURGE BINARY LOGS TO 'binlog.000027'; SHOW WARNINGS;\n", $mutations);
    }

    public function test_check_is_read_only_and_invalid_purge_cutoffs_are_refused(): void
    {
        [$process, $mutations] = $this->runPolicy('check', []);
        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());
        $this->assertSame('', $mutations);

        foreach (["binlog.000027'; SELECT 1; --", 'binlog.999999', '../binlog.000027'] as $cutoff) {
            [$process, $mutations] = $this->runPolicy('purge', $this->verifiedFlags() + ['BINLOG_ARCHIVE_VERIFIED' => '1'], $cutoff);
            $this->assertFalse($process->isSuccessful());
            $this->assertSame('', $mutations);
        }
    }

    private function verifiedFlags(): array
    {
        return ['BACKUP_VERIFIED' => '1', 'RESTORE_VERIFIED' => '1', 'RECOVERY_REVIEWED' => '1'];
    }

    private function runPolicy(string $mode, array $environment, string $cutoff = 'binlog.000027'): array
    {
        $root = dirname(__DIR__, 3);
        $bash = PHP_OS_FAMILY === 'Windows' ? 'C:/Program Files/Git/bin/bash.exe' : '/bin/bash';
        if (! is_file($bash)) {
            $this->markTestSkipped('Bash is unavailable for shell policy tests.');
        }
        $directory = sys_get_temp_dir().'/partsmall-policy-'.bin2hex(random_bytes(8));
        mkdir($directory, 0700);
        copy($root.'/tests/Fixtures/docker-binlog-policy-mock.sh', $directory.'/docker');
        chmod($directory.'/docker', 0700);
        $mutationsFile = $directory.'/mutations';
        $defaults = array_fill_keys([
            'BACKUP_VERIFIED', 'RESTORE_VERIFIED', 'RECOVERY_REVIEWED', 'BINLOG_ARCHIVE_VERIFIED',
        ], '0') + ['MYSQL_BINLOG_EXPIRE_LOGS_SECONDS' => '86400', 'MYSQL_MAX_BINLOG_SIZE' => '67108864',
            'MYSQL_BINLOG_TRANSACTION_COMPRESSION' => 'ON', 'MOCK_REPLICAS' => '0', 'MOCK_PROJECT' => 'partsmall-prod'];
        $process = new Process([$bash, '-c',
            'mock="$MOCK_DIR"; if command -v cygpath >/dev/null; then mock=$(cygpath -u "$mock"); fi; '
            .'export PATH="$mock:$PATH"; bash "$POLICY_PATH" "$POLICY_MODE" "$POLICY_CUTOFF"',
        ], $root, $environment + $defaults + [
            'MOCK_DIR' => $directory, 'MOCK_MUTATIONS' => $mutationsFile,
            'POLICY_PATH' => $root.'/docker/ops/mysql-binlog-policy.sh', 'POLICY_MODE' => $mode, 'POLICY_CUTOFF' => $cutoff,
        ]);
        try {
            $process->run();

            return [$process, is_file($mutationsFile) ? file_get_contents($mutationsFile) : ''];
        } finally {
            if (is_file($mutationsFile)) {
                unlink($mutationsFile);
            }
            unlink($directory.'/docker');
            rmdir($directory);
        }
    }

    public function test_policy_requires_backup_restore_and_recovery_review_before_changes(): void
    {
        $script = file_get_contents(dirname(__DIR__, 3).'/docker/ops/mysql-binlog-policy.sh');

        $this->assertStringContainsString('mode=${1:-check}', $script);
        $this->assertStringContainsString('BACKUP_VERIFIED RESTORE_VERIFIED RECOVERY_REVIEWED', $script);
        $this->assertStringContainsString('BINLOG_ARCHIVE_VERIFIED', $script);
        $this->assertLessThan(strpos($script, 'SET PERSIST'), strpos($script, 'Refusing changes'));
        $this->assertLessThan(strpos($script, 'PURGE BINARY LOGS TO'), strpos($script, 'Verify every binlog'));
    }

    public function test_policy_is_scoped_to_production_mysql_and_preserves_the_active_log(): void
    {
        $script = file_get_contents(dirname(__DIR__, 3).'/docker/ops/mysql-binlog-policy.sh');

        $this->assertStringContainsString('container=partsmall-prod-mysql-1', $script);
        $this->assertStringContainsString('= partsmall-prod', $script);
        $this->assertStringContainsString('= partsmall-prod-mysql', $script);
        $this->assertStringContainsString('replication_connection_configuration', $script);
        $this->assertStringContainsString('SHOW REPLICAS', $script);
        $this->assertStringNotContainsString('RESET MASTER', $script);
        $this->assertStringNotContainsString('RESET BINARY LOGS', $script);
        $this->assertDoesNotMatchRegularExpression('/\brm\b|docker (?:system|volume|image) prune/', $script);
    }

    public function test_policy_keeps_source_defaults_and_runtime_defaults_in_sync(): void
    {
        $script = file_get_contents(dirname(__DIR__, 3).'/docker/ops/mysql-binlog-policy.sh');

        $this->assertStringContainsString('MYSQL_BINLOG_EXPIRE_LOGS_SECONDS:-86400', $script);
        $this->assertStringContainsString('MYSQL_MAX_BINLOG_SIZE:-67108864', $script);
        $this->assertStringContainsString('MYSQL_BINLOG_TRANSACTION_COMPRESSION:-ON', $script);
        $this->assertStringContainsString('SET PERSIST binlog_expire_logs_auto_purge=ON', $script);
        $this->assertStringContainsString('size % 4096', $script);
    }
}
