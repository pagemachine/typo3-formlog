<?php

declare(strict_types=1);

namespace Pagemachine\Formlog\Tests\Functional\Domain\Form\Finishers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request as ExtbaseRequest;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Form\Domain\Factory\ArrayFormFactory;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime\FormSession;
use TYPO3\CMS\Form\Domain\Runtime\FormState;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Testcase for Pagemachine\Formlog\Domain\Form\Finishers\LoggerFinisher
 */
final class LoggerFinisherTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'form',
    ];

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/formlog',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TYPO3_CONF_VARS']['BE']['lockRootPath'] = ORIGINAL_ROOT;

        $this->getConnectionPool()->getConnectionForTable('pages')->insert('pages', ['uid' => 123]);
        $this->setUpFrontendRootPage(123);

        $siteConfiguration = GeneralUtility::makeInstance(SiteConfiguration::class);
        $siteConfiguration->createNewBasicSite('123', 123, 'http://localhost/');

        $_SERVER['HTTP_HOST'] = 'localhost';
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByRootPageId(123);
        $siteLanguage = $site->getLanguageById(0);
        $frontendUser = GeneralUtility::makeInstance(FrontendUserAuthentication::class);
        $frontendUser->initializeUserSessionManager();

        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            GeneralUtility::makeInstance(Context::class),
            $site,
            $siteLanguage,
            new PageArguments(123, '0', []),
            $frontendUser,
        );

        $requestFactory = GeneralUtility::makeInstance(ServerRequestFactory::class);
        $request = $requestFactory->createServerRequest('get', 'http://localhost');

        $GLOBALS['TSFE']->determineId($request);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TSFE']);
    }

    #[DataProvider('formData')]
    #[Test]
    public function logsSubmittedFormData(array $fields, array $formValues, string $expectedData)
    {
        $formDefinition = $this->buildFormDefinition([
            'renderables' => [
                'page' => [
                    'renderables' => $fields,
                ],
            ],
            'finishers' => [
                [
                    'identifier' => 'LogFormData',
                ],
            ],
        ]);

        $this->submitForm($formDefinition, $formValues);

        $logEntry = $this->getConnectionPool()->getConnectionForTable('tx_formlog_entries')
            ->select(['*'], 'tx_formlog_entries')
            ->fetchAssociative();

        self::assertSame(123, $logEntry['page'] ?? null);
        self::assertSame($formDefinition->getIdentifier(), $logEntry['identifier'] ?? null);
        self::assertSame($expectedData, $logEntry['data'] ?? null);
        self::assertSame('[]', $logEntry['finisher_variables'] ?? null);
    }

    public static function formData(): \Generator
    {
        yield 'basic' => [
            [
                [
                    'identifier' => 'name',
                    'type' => 'Text',
                ],
            ],
            [
                'name' => 'Tester',
            ],
            '{"name":"Tester"}',
        ];

        yield 'date' => [
            [
                [
                    'identifier' => 'date',
                    'type' => 'Date',
                ],
            ],
            [
                'date' => '2022-02-07',
            ],
            '{"date":"07.02.2022"}',
        ];

        yield 'date with custom display format' => [
            [
                [
                    'identifier' => 'date',
                    'type' => 'Date',
                    'properties' => [
                        'displayFormat' => 'Y-m-d',
                    ],
                ],
            ],
            [
                'date' => '2022-02-07',
            ],
            '{"date":"2022-02-07"}',
        ];

        // TODO: Research why "DatePicker" fails completely

        $temporaryFilePath = tempnam(sys_get_temp_dir(), 'LoggerFinisherTest');
        file_put_contents($temporaryFilePath, 'Test file for upload');

        yield 'file upload' => [
            [
                [
                    'identifier' => 'upload',
                    'type' => 'FileUpload',
                    'properties' => [
                        'saveToFileMount' => '1:/',
                        'allowedMimeTypes' => [
                            'text/plain',
                        ],
                    ],
                ],
            ],
            [
                'upload' => [
                    'name' => 'test.txt',
                    'type' => 'text/plain',
                    'size' => filesize($temporaryFilePath),
                    'tmp_name' => $temporaryFilePath,
                    'error' => UPLOAD_ERR_OK,
                ],
            ],
            '{"upload":{"file":{"name":"test.txt"}}}',
        ];
    }

    #[Test]
    public function logsFinisherVariables()
    {
        $formDefinition = $this->buildFormDefinition([
            'renderables' => [
                'page' => [
                    'renderables' => [
                        [
                            'identifier' => 'name',
                            'type' => 'Text',
                        ],
                    ],
                ],
            ],
            'finishers' => [
                [
                    'identifier' => 'SaveToDatabase',
                    'options' => [
                        'table' => 'pages',
                        'databaseColumnMappings' => [
                            'title' => [
                                'value' => 'Page created by form',
                            ],
                        ],
                    ],
                ],
                [
                    'identifier' => 'LogFormData',
                    'options' => [
                        'finisherVariables' => [
                            'SaveToDatabase' => [
                                'insertedUids.0',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->submitForm($formDefinition, [
            'name' => 'Tester',
        ]);

        $logEntry = $this->getConnectionPool()->getConnectionForTable('tx_formlog_entries')
            ->select(['*'], 'tx_formlog_entries')
            ->fetchAssociative();

        self::assertSame(123, $logEntry['page'] ?? null);
        self::assertSame($formDefinition->getIdentifier(), $logEntry['identifier'] ?? null);
        self::assertSame('{"name":"Tester"}', $logEntry['data'] ?? null);
        self::assertSame('{"SaveToDatabase":{"insertedUids.0":124}}', $logEntry['finisher_variables'] ?? null);
    }

    protected function buildFormDefinition(array $configuration): FormDefinition
    {
        $commonConfiguration = [
            'prototypeName' => 'standard',
            'identifier' => 'LoggerFinisherTest',
            'renderables' => [
                'page' => [
                    'identifier' => 'page1',
                    'type' => 'Page',
                ],
            ],
        ];

        $arrayFormFactory = GeneralUtility::makeInstance(ArrayFormFactory::class);
        $formDefinition = $arrayFormFactory->build(array_merge_recursive($commonConfiguration, $configuration));

        return $formDefinition;
    }

    protected function submitForm(FormDefinition $formDefinition, array $formValues = [])
    {
        $formState = GeneralUtility::makeInstance(FormState::class);
        $formState->setLastDisplayedPageIndex(0);

        foreach ($formValues as $name => $value) {
            $formState->setFormValue($name, $value);
        }

        $requestArguments = array_merge(
            $formValues,
            [
                '__currentPage' => 1,
                '__state' => GeneralUtility::makeInstance(HashService::class)->appendHmac(base64_encode(serialize($formState))),
                '__session' => GeneralUtility::makeInstance(FormSession::class)->getAuthenticatedIdentifier(),
            ]
        );

        $contentObjectRenderer = new ContentObjectRenderer();
        $contentObjectRenderer->setUserObjectType(ContentObjectRenderer::OBJECTTYPE_USER_INT);

        $requestFactory = GeneralUtility::makeInstance(ServerRequestFactory::class);
        $serverRequest = $requestFactory->createServerRequest('POST', 'http://localhost')
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('extbase', GeneralUtility::makeInstance(ExtbaseRequestParameters::class))
            ->withAttribute('currentContentObject', $contentObjectRenderer);

        $request = GeneralUtility::makeInstance(ExtbaseRequest::class, $serverRequest)->withArguments([
            $formDefinition->getIdentifier() => $requestArguments,
        ]);
        $formRuntime = $formDefinition->bind($request);

        $formRuntime->render();
    }
}
