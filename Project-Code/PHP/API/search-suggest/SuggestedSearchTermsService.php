<?php

namespace LAZ\objects\kidsaz\services\bookroom;

use LAZ\objects\tools\raz\SearchHelp;

class SuggestedSearchTermsService {
    const MAX_NUMBER_OF_TERMS = 5;
    private $searchHelp;

    public function __construct() {
        $this->searchHelp = new SearchHelp();
    }

    public function getSuggestedTermsBySearchTerms($searchText) {
        $searchText = trim($searchText);
        $this->searchHelp->executeSearchHistorySearch($searchText);
        $searchItems = $this->searchHelp->getSearchResultsList();

        $suggestedTerms = [];
        if (is_array($searchItems)) {
            foreach ($searchItems as $searchItem) {
                if (count($suggestedTerms) >= self::MAX_NUMBER_OF_TERMS) break;
                $suggestedTerms[] = array('value' => $searchItem['searchTerms']);
            }
        }

        return $suggestedTerms;
    }

}