<?php

declare(strict_types=1);

use App\Services\NormalizationService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Validates normalizeLastPunchData against representative DownloadLastPunchData shapes
 * (NEXT_STEPS: API envelope + normalization compatibility).
 *
 * @internal
 */
final class NormalizationLastPunchDataTest extends CIUnitTestCase
{
    private NormalizationService $normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = new NormalizationService();
    }

    public function testPunchDataWrapperWithLastRecordId(): void
    {
        $api = [
            'PunchData' => [
                [
                    'Empcode' => 'E001',
                    'Name' => 'Test User',
                    'DateTimeRecord' => '25/04/2026 09:15:00',
                ],
            ],
            'LastRecordId' => '042026$00000123',
        ];

        $out = $this->normalizer->normalizeLastPunchData($api);

        $this->assertSame('042026$00000123', $out['last_record_id']);
        $this->assertCount(1, $out['records']);
        $this->assertSame('E001', $out['records'][0]['emp_code']);
        $this->assertSame('2026-04-25 09:15:00', $out['records'][0]['punch_time']);
        $this->assertSame('DownloadLastPunchData', $out['records'][0]['source']);
    }

    public function testDownloadLastPunchDataKeyWrapper(): void
    {
        $api = [
            'DownloadLastPunchData' => [
                ['EmpCode' => 'E002', 'PunchTime' => '2026-04-25 14:30:00'],
            ],
            'LastRecord' => '042026$00000999',
        ];

        $out = $this->normalizer->normalizeLastPunchData($api);

        $this->assertSame('042026$00000999', $out['last_record_id']);
        $this->assertCount(1, $out['records']);
        $this->assertSame('E002', $out['records'][0]['emp_code']);
        $this->assertSame('2026-04-25 14:30:00', $out['records'][0]['punch_time']);
    }

    public function testSnakeCaseLastRecordAndFlatList(): void
    {
        $api = [
            [
                'emp_code' => 'E003',
                'name' => 'Flat List',
                'punch_time' => '25/04/2026 18:00',
            ],
            'last_record_id' => '042026$00000001',
        ];

        $out = $this->normalizer->normalizeLastPunchData($api);

        $this->assertSame('042026$00000001', $out['last_record_id']);
        $this->assertCount(1, $out['records']);
        $this->assertSame('E003', $out['records'][0]['emp_code']);
    }

    public function testSeparateDateAndTimeFields(): void
    {
        $api = [
            'PunchData' => [
                [
                    'Empcode' => 'E004',
                    'Date' => '25/04/2026',
                    'Time' => '08:45',
                ],
            ],
        ];

        $out = $this->normalizer->normalizeLastPunchData($api);

        $this->assertNull($out['last_record_id']);
        $this->assertCount(1, $out['records']);
        $this->assertSame('2026-04-25 08:45:00', $out['records'][0]['punch_time']);
    }

    public function testEmptyPayloadYieldsNoRecords(): void
    {
        $out = $this->normalizer->normalizeLastPunchData([]);

        $this->assertSame([], $out['records']);
        $this->assertNull($out['last_record_id']);
    }

    public function testSkipsRecordsWithoutEmpCodeOrPunchTime(): void
    {
        $api = [
            'PunchData' => [
                ['Name' => 'No code'],
                ['Empcode' => 'E005'],
            ],
        ];

        $out = $this->normalizer->normalizeLastPunchData($api);

        $this->assertSame([], $out['records']);
    }
}
