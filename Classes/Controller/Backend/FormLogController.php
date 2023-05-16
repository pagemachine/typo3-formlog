<?php

declare(strict_types = 1);

namespace Pagemachine\Formlog\Controller\Backend;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Pagemachine\Formlog\Domain\FormLog\Filters;
use Pagemachine\Formlog\Domain\Repository\FormLogEntryRepository;
use Pagemachine\Formlog\Mvc\View\Export\CsvView;
use Pagemachine\Formlog\Mvc\View\Export\XlsxView;
use Pagemachine\Formlog\Mvc\View\FormatViewResolver;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\View\BackendTemplateView;
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
    /**
     * @var \Pagemachine\Formlog\Mvc\View\ConfigurableViewInterface
     */
    protected $view = null;

    protected array $viewFormatToObjectNameMap = [
        'csv' => CsvView::class,
        'xlsx' => XlsxView::class,
    ];

    /**
     * @var string
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    protected FormLogEntryRepository $formLogEntryRepository;

    public function injectFormLogEntryRepository(FormLogEntryRepository $formLogEntryRepository)
    {
        $this->formLogEntryRepository = $formLogEntryRepository;
    }

    /**
     * Initialize all actions
     *
     * @return void
     */
    public function initializeAction()
    {
        if ($this->arguments->hasArgument('filters')) {
            $this->request->setArgument('filters', $this->request->hasArgument('filters') ? $this->request->getArgument('filters') : []);
            $filtersArgument = $this->arguments->getArgument('filters');
            $filtersArgument->getPropertyMappingConfiguration()
                ->allowAllProperties()
                ->forProperty('*')
                    ->allowAllProperties();
        }
    }

    /**
     * Main overview action
     *
     * @param Filters $filters
     * @param int $currentPageNumber
     * @return void
     */
    public function indexAction(Filters $filters, int $currentPageNumber = 1)
    {
        $entries = $this->formLogEntryRepository->findAllFiltered($filters);
        $paginator = new QueryResultPaginator($entries, $currentPageNumber);
        /** @var UriBuilder */
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
                    'language' => $GLOBALS['BE_USER']->uc['lang'],
                    'timeZone' => date_default_timezone_get(),
                ],
            ],
        ]);
    }

    public function initializeExportAction(): void
    {
        $viewResolver = GeneralUtility::makeInstance(FormatViewResolver::class);

        foreach ($this->viewFormatToObjectNameMap as $format => $viewClassName) {
            $viewResolver->map($format, $viewClassName);
        }

        $this->injectViewResolver($viewResolver);
    }

    /**
     * Export CSV of form log entries
     *
     * @param Filters $filters
     * @return void
     */
    public function exportAction(Filters $filters)
    {
        $now = new \DateTime();
        $fileBasename = sprintf('formlog-%s', $now->format('Y-m-d-H-i-s'));

        $this->view->setConfiguration([
            'columns' => $this->settings['export']['columns'],
            'dateTimeFormat' => $this->settings['dateTimeFormat'],
            'fileBasename' => $fileBasename,
        ]);
        $this->view->assign('items', $this->formLogEntryRepository->findAllFiltered($filters));
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
                'applyButtonTitle',
                'cancelButtonTitle',
                'startLabel',
                'endLabel',
            ],
            'ranges' => [
                'last30days',
                'lastYear',
                'other',
            ],
            'periods' => [
                'day',
                'week',
                'month',
                'quarter',
                'year',
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
