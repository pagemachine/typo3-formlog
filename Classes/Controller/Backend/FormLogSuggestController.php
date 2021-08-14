<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Controller\Backend;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Pagemachine\Formlog\Domain\FormLog\Suggestions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\Response;

/**
 * Controller for form log suggestions
 */
final class FormLogSuggestController
{
    /**
     * @var Suggestions
     */
    private $suggestions;

    public function __construct(Suggestions $suggestions)
    {
        $this->suggestions = $suggestions;
    }

    public function searchAction(ServerRequestInterface $request): ResponseInterface
    {
        $body = (array)$request->getParsedBody();
        $suggestions = $this->suggestions->getForProperty($body['property']);
        $response = (new Response())->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(json_encode($suggestions));

        return $response;
    }
}
