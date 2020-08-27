<?php

namespace LAZ\objects\kidsaz\api\SuggestedSearchTerms;

use LAZ\objects\library\Router\ControllerRouter;
use Psr\Http\Message\ServerRequestInterface;

class SuggestedSearchTermsRouter extends ControllerRouter {
    public function __construct() {
        parent::__construct(SuggestedSearchTermsApiController::class, '/search-suggest');
    }

    protected function registerRoutes() {
        $this->get("", function (SuggestedSearchTermsApiController $controller, ServerRequestInterface $request) {
            return $controller->getSuggestedTerms($request);
        });
    }
}