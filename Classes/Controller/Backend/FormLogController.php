<?php

declare(strict_types=1);

namespace Pagemachine\Formlog\Controller\Backend;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Pagemachine\Formlog\Domain\FormLog\Filters;
use Pagemachine\Formlog\Domain\FormLog\ValueFilter;
use Pagemachine\Formlog\Domain\Repository\FormLogEntryRepository;
use Pagemachine\Formlog\Export\CsvExport;
use Pagemachine\Formlog\Export\XlsxExport;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller for form log management
 */
class FormLogController extends ActionController
{
    protected array $viewFormatToExportMap = [
        'csv' => CsvExport::class,
        'xlsx' => XlsxExport::class,
    ];

    protected FormLogEntryRepository $formLogEntryRepository;

    public function __construct(protected ModuleTemplateFactory $moduleTemplateFactory) {}

    public function injectFormLogEntryRepository(FormLogEntryRepository $formLogEntryRepository)
    {
        $this->formLogEntryRepository = $formLogEntryRepository;
    }

    /**
     * Initialize all actions
     */
    public function initializeAction()
    {
        if ($this->arguments->hasArgument('filters')) {
            $filters = $this->request->hasArgument('filters') ? $this->request->getArgument('filters') : [];

            if ((new Typo3Version())->getMajorVersion() < 11) {
                $this->request->setArgument('filters', $filters);
            } else {
                $this->request = $this->request->withArgument('filters', $filters);
            }

            $filtersArgument = $this->arguments->getArgument('filters');
            $filtersArgument->getPropertyMappingConfiguration()
                ->allowAllProperties()
                ->forProperty('*')
                    ->allowAllProperties();
        }
    }

    /**
     * Main overview action
     */
    public function indexAction(Filters $filters, int $currentPageNumber = 1): ResponseInterface
    {
        $entries = $this->formLogEntryRepository->findAllFiltered($filters);
        $paginator = new QueryResultPaginator($entries, $currentPageNumber);
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        $this->view->assignMultiple([
            'entries' => $paginator->getPaginatedItems(),
            'entriesCount' => count($entries),
            'filters' => $filters,
            'pagination' => new SimplePagination($paginator),
            'currentPageNumber' => $currentPageNumber,
            'dateFormat' => $this->settings['dateTimeFormat'] ?: \DateTime::W3C,
            'isoDateFormat' => \DateTime::W3C,
            'daterangepickerTranslations' => $this->prepareDaterangepickerTranslations(),
            'inlineSettings' => [
                'formlog' => [
                    'suggestUri' => (string)$uriBuilder->buildUriFromRoute('ajax_formlog_suggest'),
                    'language' => $GLOBALS['BE_USER']->user['lang'],
                ],
            ],
        ]);

        GeneralUtility::makeInstance(PageRenderer::class)->addRequireJsConfiguration([
            'paths' => [
                'TYPO3/CMS/Formlog/moment' => 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment-with-locales.min',
            ],
        ]);

        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->setContent($this->view->render());

        return $this->htmlResponse($moduleTemplate->renderContent());
    }

    /**
     * Export CSV of form log entries
     */
    public function exportAction(Filters $filters, string $type = ''): ResponseInterface
    {
        $export = GeneralUtility::makeInstance($this->viewFormatToExportMap[$this->request->getFormat()]);

        $now = new \DateTime();

        $columns = $this->settings['export']['columns'];
        $fileBasename = sprintf('formlog-%s', $now->format('Y-m-d-H-i-s'));
        if($type) {
            $fileBasename = "{$this->settings['export']['types'][$type]['label']}_{$now->format('d-m-Y')}";
            $fileBasename = (new \TYPO3\CMS\Core\Resource\Driver\LocalDriver)->sanitizeFileName($fileBasename);
            $identifierFilter = new ValueFilter($this->settings['export']['types'][$type]['formIdentifier']);
            $filters->setIdentifier($identifierFilter);
            $columns = $this->settings['export']['types'][$type]['columns'];
            $valueMapper = $this->settings['export']['types'][$type]['valueMapper'];
        }
        $logEntries = $this->formLogEntryRepository->findAllFiltered($filters);

        $export->setConfiguration([
            'columns' => $columns,
            'dateTimeFormat' => $this->settings['dateTimeFormat'],
            'fileBasename' => $fileBasename,
            'valueMapper' => $valueMapper ?? [],
        ]);

        $export->dump($logEntries);

        return $this->responseFactory->createResponse();
    }

    /**
     * Prepare localized daterangepicker labels
     *
     * @return array
     */
    protected function prepareDaterangepickerTranslations(): array
    {
        $translationIdentifiers = [
            'labels' => [
                'applyLabel',
                'cancelLabel',
                'fromLabel',
                'toLabel',
                'customRangeLabel',
            ],
            'ranges' => [
                'last30days',
                'lastYear',
            ],
        ];
        $translations = [];

        foreach ($translationIdentifiers as $section => $identifiers) {
            foreach ($identifiers as $identifier) {
                $translations[$section][$identifier] = LocalizationUtility::translate('formlog.daterangepicker.' . $section . '.' . $identifier, 'Formlog');
            }
        }

        return $translations;
    }
}
