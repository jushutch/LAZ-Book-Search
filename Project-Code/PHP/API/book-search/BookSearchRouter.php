<?php

namespace LAZ\objects\kidsaz\api\BookSearch;

use LAZ\objects\library\Router\ControllerRouter;
use Psr\Http\Message\ServerRequestInterface;

class BookSearchRouter extends ControllerRouter {

    public function __construct() {
        parent::__construct(BookSearchApiController::class, '/books');
    }

    protected  function registerRoutes() {
        $this->get("", function (BookSearchApiController $controller, ServerRequestInterface $request) {
            return $controller->searchBooks($request);
        });
    }
}