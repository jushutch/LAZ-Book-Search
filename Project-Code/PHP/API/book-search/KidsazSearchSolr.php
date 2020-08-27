<?php

namespace LAZ\objects\tools\kidsaz;

use LAZ\objects\data\solr\SolrJsonUtils;
use LAZ\objects\data\solr\SolrSearch;
use LAZ\objects\shared\businessObjects\Solr\QueryField;

class KidsazSearchSolr {
    const MAX_NUMBER_OF_SOLR_RESULTS = 2000;
    private $kidsazSolrFilter;

    public function __construct(array $bookroomConfigs, int $studentLevelId,  int $studentPrimaryLanguageId) {
        $this->kidsazSolrFilter = new KidsazSolrFilter($bookroomConfigs, $studentLevelId, $studentPrimaryLanguageId);
    }

    public function getSearchResults(string $searchString, array $availableBookroomCollections, bool $hasRazPlus) {
        $core = $hasRazPlus ? "raz" : "rk";
        $solrSearch = new SolrSearch($core);
        $solrSearch->setQueryTerm(utf8_encode($searchString));

//        Query Fields
        $solrSearch->addMultipleQueryFields($this->getQueryFields());

//        Filter queries
        $filterQueryString = $this->kidsazSolrFilter->getFilterQueryStringForBookroomCollections($availableBookroomCollections);
        $solrSearch->addFilterQueryString($filterQueryString);
        if (!$hasRazPlus) $solrSearch->addFilterQuery(SolrConstants::FIELD_RAZ_REQUIRED, "false");

//        Result Fields
        $solrSearch->addMultipleResultSetFields($hasRazPlus ? SolrConstants::$RAZ_RESULT_FIELDS : SolrConstants::$RK_RESULT_FIELDS);

//        Additional Parameters
        $solrSearch->indentResultSet( true);
        $solrSearch->setMinimumMatch( $this->getNumberOfSearchTerms($searchString));
        $solrSearch->setStopwords( true);
        $solrSearch->setSpellcheck(false);
        $additionalRequestParams = $this->kidsazSolrFilter->getAdditionalParameters();
        $solrJsonResponse = $solrSearch->fetch(0, self::MAX_NUMBER_OF_SOLR_RESULTS, $additionalRequestParams);
        return $this->getSearchResultsFromSolrJsonResponse($solrJsonResponse, $hasRazPlus);
    }

    private function getSearchResultsFromSolrJsonResponse(string $solrJsonResponse, bool $hasRazPlus) {
        $solrResponse = SolrJsonUtils::createArray($solrJsonResponse);
        $docs = $solrResponse['response']['docs'];
        return $this->getSearchItemsFromDocs($docs, $hasRazPlus);
    }

    private function getSearchItemsFromDocs(array $docs, bool $hasRazPlus) {
        $resultFields = $hasRazPlus ? SolrConstants::$RAZ_RESULT_FIELDS : SolrConstants::$RK_RESULT_FIELDS;
        $searchResults = [];
        foreach ($docs as $doc) {
            $searchResult = [];
            foreach ($resultFields as $item => $field) {
                if(isset($doc[$field])) {
                    $searchResult[$item] = $doc[$field];
                }
            }
            $searchResults[] = $searchResult;
        }
        return $searchResults;
    }

    private function getNumberOfSearchTerms(string $searchTerms){
        $terms = explode( " ", $searchTerms);
        return count($terms);
    }

    private function getQueryFields() {
        return array_map(function($name, $boost) {
            return new QueryField($name, $boost);
        }, array_keys(SolrConstants::$KIDSAZ_QUERY_FIELDS_BOOST), SolrConstants::$KIDSAZ_QUERY_FIELDS_BOOST);
    }
}
