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
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller for form log management
 */
class FormLogController extends ActionController
{
    /**
     * @var \TYPO3\CMS\Extbase\Mvc\View\ViewInterface|\Pagemachine\Formlog\Mvc\View\ConfigurableViewInterface
     */
    protected $view = null;

    /**
     * @var array
     */
    protected $viewFormatToObjectNameMap = [
        'csv' => CsvView::class,
        'xlsx' => XlsxView::class,
    ];

    /**
     * @var string
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    /**
     * @var FormLogEntryRepository $formLogEntryRepository
     */
    protected $formLogEntryRepository;

    /**
     * @param FormLogEntryRepository $formLogEntryRepository
     */
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
            $filters = $this->request->hasArgument('filters') ? $this->request->getArgument('filters') : [];

            if (empty($filters['pageTitle'])) {
                $filters['pageTitle'] = new \Pagemachine\Formlog\Domain\FormLog\ValueFilter('page.title');
            }

            if (empty($filters['submissionDate'])) {
                $filters['submissionDate'] = new \Pagemachine\Formlog\Domain\FormLog\DateRangeFilter('submissionDate');
            }

            $this->request->setArgument('filters', $filters);

            $filtersArgument = $this->arguments->getArgument('filters');
            $filtersMappingConfiguration = $filtersArgument->getPropertyMappingConfiguration();

            $filtersMappingConfiguration
                ->allowAllProperties()
                ->forProperty('*')
                    ->allowAllProperties();

            $filtersMappingConfiguration->forProperty('pageTitle')
                ->setTypeConverterOption(
                    \TYPO3\CMS\Extbase\Property\TypeConverter\ObjectConverter::class,
                    \TYPO3\CMS\Extbase\Property\TypeConverter\ObjectConverter::CONFIGURATION_TARGET_TYPE,
                    \Pagemachine\Formlog\Domain\FormLog\ValueFilter::class
                );

            $filtersMappingConfiguration->forProperty('submissionDate')
                ->setTypeConverterOption(
                    \TYPO3\CMS\Extbase\Property\TypeConverter\ObjectConverter::class,
                    \TYPO3\CMS\Extbase\Property\TypeConverter\ObjectConverter::CONFIGURATION_TARGET_TYPE,
                    \Pagemachine\Formlog\Domain\FormLog\DateRangeFilter::class
                );
        }
    }

    /**
     * Main overview action
     *
     * @param Filters $filters
     * @param array $pagination
     * @return void
     */
    public function indexAction(Filters $filters, array $pagination = [])
    {
        $this->view->assignMultiple([
            'entries' => $this->formLogEntryRepository->findAllFiltered($filters),
            'filters' => $filters,
            'pagination' => $pagination,
            'dateFormat' => $this->settings['dateTimeFormat'] ?: \DateTime::W3C,
            'isoDateFormat' => \DateTime::W3C,
            'daterangepickerTranslations' => $this->prepareDaterangepickerTranslations(),
            'inlineSettings' => [
                'formlog' => [
                    'suggestUri' => (string)GeneralUtility::makeInstance(UriBuilder::class)->buildUriFromRoute('ajax_formlog_suggest'),
                    'language' => $GLOBALS['BE_USER']->uc['lang'],
                    'timeZone' => date_default_timezone_get(),
                ],
            ],
        ]);
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
