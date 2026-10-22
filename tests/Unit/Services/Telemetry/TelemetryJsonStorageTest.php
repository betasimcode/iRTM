<?php

namespace Tests\\Unit\\Services\\Telemetry;

use App\\Services\\Telemetry\\TelemetryJsonStorage;
use RuntimeException;
use Tests\\TestCase;

class TelemetryJsonStorageTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir() . '/irtm-telemetry-json-' . bin2hex(random_bytes(6));
        mkdir($this->directory, 0775, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory . '/*') ?: [] as $file) {
            @unlink($file);
        }

        @rmdir($this->directory);

        parent::tearDown();
    }

    public function test_it_writes_gzip_and_reads_it_using_the_logical_json_path(): void
    {
        $storage = new TelemetryJsonStorage();
        $logicalPath = $this->directory . '/lap_001.json';
        $payload = [
            'format_version' => 1,
            'candidate_lap' => 1,
            'samples' => [
                ['speed_kph' => 123.45, 'gear' => 4],
                ['speed_kph' => 124.56, 'gear' => 5],
            ],
        ];

        $physicalPath = $storage->writeJson($logicalPath, $payload);

        $this->assertSame($logicalPath . '.gz', $physicalPath);
        $this->assertFileExists($physicalPath);
        $this->assertSame($payload, $storage->readJson($logicalPath));
        $this->assertTrue($storage->exists($logicalPath));
    }

    public function test_it_reads_legacy_uncompressed_json(): void
    {
        $storage = new TelemetryJsonStorage();
        $path = $this->directory . '/session.json';
        $payload = ['track' => ['id' => 123]];

        file_put_contents($path, json_encode($payload, JSON_THROW_ON_ERROR));

        $this->assertSame($payload, $storage->readJson($path));
    }

    public function test_it_rejects_invalid_json(): void
    {
        $storage = new TelemetryJsonStorage();
        $path = $this->directory . '/invalid.json';
        file_put_contents($path, '{invalid');

        $this->expectException(RuntimeException::class);

        $storage->readJson($path);
    }
}
