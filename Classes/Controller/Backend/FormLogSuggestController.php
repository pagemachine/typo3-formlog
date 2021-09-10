<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Controller\Backend;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Pagemachine\Formlog\Domain\FormLog\Suggestions;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Controller for form log suggestions
 */
final class FormLogSuggestController
{
    private Suggestions $suggestions;

    private ResponseFactoryInterface $responseFactory;

    public function __construct(Suggestions $suggestions, ResponseFactoryInterface $responseFactory)
    {
        $this->suggestions = $suggestions;
        $this->responseFactory = $responseFactory;
    }

    public function searchAction(ServerRequestInterface $request): ResponseInterface
    {
        $body = (array)$request->getParsedBody();
        $suggestions = $this->suggestions->getForProperty($body['property']);
        $response = $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(json_encode($suggestions));

        return $response;
    }
}
