<?php

declare(strict_types=1);

namespace Pagemachine\Formlog\Tests\Functional\Domain\Form\Finishers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Tests\Functional\Framework\FormHandling\FormDataFactory;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Testcase for Pagemachine\Formlog\Domain\Form\Finishers\LoggerFinisher
 */
final class LoggerFinisherTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'fluid_styled_content',
        'form',
    ];

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/formlog',
    ];

    protected array $pathsToLinkInTestInstance = [
        'typo3conf/ext/formlog/Tests/Functional/Domain/Form/Finishers/Fixtures/FormDefinitions' => 'fileadmin/form_definitions',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnectionPool()->getConnectionForTable('pages')->insert('pages', ['uid' => 123]);
        $this->setUpFrontendRootPage(123, [
            'EXT:formlog/Tests/Functional/Domain/Form/Finishers/Fixtures/TypoScript/page.typoscript',
        ]);

        $siteConfiguration = GeneralUtility::makeInstance(SiteConfiguration::class);
        $siteConfiguration->createNewBasicSite('123', 123, 'http://localhost/');
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TSFE']);
    }

    #[DataProvider('formData')]
    #[Test]
    public function logsSubmittedFormData(
        string $formPersistenceIdentifier,
        string $formIdentifier,
        array $formValues,
        string $expectedData,
    ): void {
        $formContentUid = $this->createFormContentElement($formPersistenceIdentifier);
        $formIdentifier = sprintf('%s-%d', $formIdentifier, $formContentUid);

        $pageRequest = (new InternalRequest())->withPageId(123);
        $response = $this->executeFrontendSubRequest($pageRequest);

        self::assertSame(200, $response->getStatusCode());

        $formData = (new FormDataFactory())->fromHtmlMarkupAndXpath((string)$response->getBody(), '//form[@id="' . $formIdentifier . '"]');

        foreach ($formValues as $identifier => $value) {
            $formData = $formData->with($identifier, $value);
        }

        $formSubmitRequest = $formData->toPostRequest($pageRequest);

        $response = $this->executeFrontendSubRequest($formSubmitRequest);

        self::assertSame(200, $response->getStatusCode());

        $logEntry = $this->getConnectionPool()->getConnectionForTable('tx_formlog_entries')
            ->select(['*'], 'tx_formlog_entries')
            ->fetchAssociative();

        self::assertSame(123, $logEntry['page'] ?? null);
        self::assertSame($formIdentifier, $logEntry['identifier'] ?? null);
        self::assertSame($expectedData, $logEntry['data'] ?? null);
        self::assertSame('[]', $logEntry['finisher_variables'] ?? null);
    }

    #[Test]
    public function logsSubmittedFileData(): void
    {
        $formContentUid = $this->createFormContentElement('1:/form_definitions/FileUpload.form.yaml');
        $formIdentifier = sprintf('FileUpload-%d', $formContentUid);

        $pageRequest = (new InternalRequest())->withPageId(123);
        $response = $this->executeFrontendSubRequest($pageRequest);

        self::assertSame(200, $response->getStatusCode());

        $formData = (new FormDataFactory())->fromHtmlMarkupAndXpath((string)$response->getBody(), '//form[@id="' . $formIdentifier . '"]');

        $formSubmitRequest = $formData->toPostRequest($pageRequest);

        $temporaryFilePath = tempnam(sys_get_temp_dir(), 'LoggerFinisherTest');
        file_put_contents($temporaryFilePath, 'Test file for upload');

        $formSubmitRequest = $formSubmitRequest->withUploadedFiles([
            'tx_form_formframework' => [
                $formIdentifier => [
                    'upload' => new UploadedFile(
                        input: $temporaryFilePath,
                        size: 20,
                        clientFilename: 'test.txt',
                        clientMediaType: 'text/plain',
                        errorStatus: \UPLOAD_ERR_OK,
                    ),
                ],
            ],
        ]);

        $response = $this->executeFrontendSubRequest($formSubmitRequest);

        self::assertSame(200, $response->getStatusCode());

        $logEntry = $this->getConnectionPool()->getConnectionForTable('tx_formlog_entries')
            ->select(['*'], 'tx_formlog_entries')
            ->fetchAssociative();
        $expectedData = '{"upload":{"file":{"name":"test.txt"}}}';

        self::assertSame(123, $logEntry['page'] ?? null);
        self::assertSame($formIdentifier, $logEntry['identifier'] ?? null);
        self::assertSame($expectedData, $logEntry['data'] ?? null);
        self::assertSame('[]', $logEntry['finisher_variables'] ?? null);
    }

    public static function formData(): \Generator
    {
        yield [
            '1:/form_definitions/Basic.form.yaml',
            'Basic',
            [
                'name' => 'Tester',
            ],
            '{"name":"Tester"}',
        ];

        yield [
            '1:/form_definitions/Date.form.yaml',
            'Date',
            [
                'date' => '2022-02-07',
            ],
            '{"date":"07.02.2022"}',
        ];

        yield [
            '1:/form_definitions/DateCustomDisplayFormat.form.yaml',
            'DateCustomDisplayFormat',
            [
                'date' => '2022-02-07',
            ],
            '{"date":"2022-02-07"}',
        ];

        // TODO: Research why "DatePicker" fails completely
    }

    #[Test]
    public function logsFinisherVariables(): void
    {
        $formContentUid = $this->createFormContentElement('1:/form_definitions/FinisherVariables.form.yaml');
        $formIdentifier = sprintf('FinisherVariables-%d', $formContentUid);

        $pageRequest = (new InternalRequest())->withPageId(123);
        $response = $this->executeFrontendSubRequest($pageRequest);

        self::assertSame(200, $response->getStatusCode());

        $formData = (new FormDataFactory())->fromHtmlMarkupAndXpath((string)$response->getBody(), '//form[@id="' . $formIdentifier . '"]');
        $formSubmitRequest = $formData->with('name', 'Tester')->toPostRequest($pageRequest);
        $response = $this->executeFrontendSubRequest($formSubmitRequest);

        self::assertSame(200, $response->getStatusCode());

        $logEntry = $this->getConnectionPool()->getConnectionForTable('tx_formlog_entries')
            ->select(['*'], 'tx_formlog_entries')
            ->fetchAssociative();

        self::assertSame(123, $logEntry['page'] ?? null);
        self::assertSame($formIdentifier, $logEntry['identifier'] ?? null);
        self::assertSame('{"name":"Tester"}', $logEntry['data'] ?? null);
        self::assertSame('{"SaveToDatabase":{"insertedUids.0":124}}', $logEntry['finisher_variables'] ?? null);
    }

    private function createFormContentElement(string $formPersistenceIdentifier): int
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tt_content');
        $connection->insert('tt_content', [
            'uid' => 1,
            'pid' => 123,
            'CType' => 'form_formframework',
            'pi_flexform' => <<<XML
                <?xml version="1.0" encoding="utf-8" standalone="yes" ?>
                <T3FlexForms>
                    <data>
                        <sheet index="sDEF">
                            <language index="lDEF">
                                <field index="settings.persistenceIdentifier">
                                    <value index="vDEF">{$formPersistenceIdentifier}</value>
                                </field>
                            </language>
                        </sheet>
                    </data>
                </T3FlexForms>
            XML,
        ]);

        return (int)$connection->lastInsertId('tt_content');
    }
}
