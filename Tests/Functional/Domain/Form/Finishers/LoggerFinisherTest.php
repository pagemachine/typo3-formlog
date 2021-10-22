<?php

declare(strict_types = 1);

namespace Pagemachine\Formlog\Tests\Functional\Domain\Form\Finishers;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Form\Domain\Configuration\ConfigurationService;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime\FormSession;
use TYPO3\CMS\Form\Domain\Runtime\FormState;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Testcase for Pagemachine\Formlog\Domain\Form\Finishers\LoggerFinisher
 */
final class LoggerFinisherTest extends FunctionalTestCase
{
    /**
     * @var array
     */
    protected $coreExtensionsToLoad = [
        'form',
    ];

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/formlog',
    ];

    protected ObjectManager $objectManager;

    protected function setUp(): void
    {
        parent::setUp();

        Bootstrap::initializeLanguageObject();

        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $configurationManager = $this->objectManager->get(ConfigurationManagerInterface::class);
        $contentObjectRenderer = new ContentObjectRenderer();
        $contentObjectRenderer->setUserObjectType(ContentObjectRenderer::OBJECTTYPE_USER_INT);
        $configurationManager->setContentObject($contentObjectRenderer);

        $this->getDatabaseConnection()->insertArray('pages', ['uid' => 123]);
        $this->setUpFrontendRootPage(123);
    }

    /**
     * @test
     */
    public function logsSubmittedFormData()
    {
        $formDefinition = $this->buildFormDefinition([
            'LogFormData' => [],
        ]);

        $this->submitForm($formDefinition, [
            'name' => 'Tester',
        ]);

        $logEntry = $this->getDatabaseConnection()->selectSingleRow('*', 'tx_formlog_entries', '1=1');

        $this->assertArraySubset([
            'pid' => 123,
            'identifier' => $formDefinition->getIdentifier(),
            'data' => '{"name":"Tester"}',
            'finisher_variables' => '[]',
        ], $logEntry);
    }

    /**
     * @test
     */
    public function logsFinisherVariables()
    {
        $formDefinition = $this->buildFormDefinition([
            'SaveToDatabase' => [
                'table' => 'pages',
                'databaseColumnMappings' => [
                    'title' => [
                        'value' => 'Page created by form',
                    ],
                ],
            ],
            'LogFormData' => [
                'finisherVariables' => [
                    'SaveToDatabase' => [
                        'insertedUids.0',
                    ],
                ],
            ],
        ]);

        $this->submitForm($formDefinition, [
            'name' => 'Tester',
        ]);

        $logEntry = $this->getDatabaseConnection()->selectSingleRow('*', 'tx_formlog_entries', '1=1');

        $this->assertArraySubset([
            'pid' => 123,
            'identifier' => $formDefinition->getIdentifier(),
            'data' => '{"name":"Tester"}',
            'finisher_variables' => '{"SaveToDatabase":{"insertedUids.0":124}}',
        ], $logEntry);
    }

    protected function buildFormDefinition(array $finishers = []): FormDefinition
    {
        $formDefinition = $this->objectManager->get(
            FormDefinition::class,
            'LogsSubmittedFormData',
            $this->objectManager->get(ConfigurationService::class)->getPrototypeConfiguration('standard')
        );
        $formDefinition->setRenderingOption('controllerAction', 'index');
        $page1 = $formDefinition->createPage('page1');
        $name = $page1->createElement('name', 'Text');

        $_SERVER['HTTP_HOST'] = 'localhost';
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByRootPageId(123);
        $siteLanguage = $site->getLanguageById(1);
        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(TypoScriptFrontendController::class, null, $site, $siteLanguage);
        $GLOBALS['TSFE']->id = 123;

        foreach ($finishers as $finisherIdentifier => $options) {
            $formDefinition->createFinisher($finisherIdentifier, $options);
        }

        unset($GLOBALS['TSFE']);

        return $formDefinition;
    }

    protected function submitForm(FormDefinition $formDefinition, array $formValues = [])
    {
        $formState = $this->objectManager->get(FormState::class);
        $formState->setLastDisplayedPageIndex(0);

        foreach ($formValues as $name => $value) {
            $formState->setFormValue($name, $value);
        }

        $requestArguments = array_merge(
            $formValues,
            [
                '__currentPage' => 1,
                '__state' => $this->objectManager->get(HashService::class)->appendHmac(base64_encode(serialize($formState))),
            ]
        );

        if (class_exists(FormSession::class)) {
            $requestArguments['__session'] = $this->objectManager->get(FormSession::class)->getAuthenticatedIdentifier();
        }

        $request = $this->objectManager->get(Request::class);
        $request->setMethod('POST');
        $request->setArguments([
            $formDefinition->getIdentifier() => $requestArguments,
        ]);
        $response = $this->objectManager->get(Response::class);
        $formRuntime = $formDefinition->bind($request, $response);

        $formRuntime->render();
    }
}
