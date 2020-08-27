<?php

namespace LAZ\objects\kidsaz\api\RecommendedBooklist;

use LAZ\objects\library\Router\ControllerRouter;
use Psr\Http\Message\ServerRequestInterface;

class RecommendedBooklistRouter extends ControllerRouter {
    public function __construct() {
        parent::__construct(RecommendedBooklistApiController::class, '/recommended');
    }

    protected  function registerRoutes() {
        $this->get("", function(RecommendedBooklistApiController $controller, ServerRequestInterface $request) {
            return $controller->getRecommendedBooklist($request);
        });
    }
}