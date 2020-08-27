<?php

namespace LAZ\objects\kidsaz\api\SuggestedSearchTerms;

use LAZ\objects\kidsaz\services\bookroom\SuggestedSearchTermsService;
use Psr\Http\Message\ServerRequestInterface;

class SuggestedSearchTermsApiController {

    private $suggestedSearchTermsService;

    public function __construct() {
        $this->suggestedSearchTermsService = new SuggestedSearchTermsService();
    }

    public function getSuggestedTerms(ServerRequestInterface $request) {
        $searchText = $request->getQueryParams()['term'];
        return $this->suggestedSearchTermsService->getSuggestedTermsBySearchTerms($searchText);
    }

}