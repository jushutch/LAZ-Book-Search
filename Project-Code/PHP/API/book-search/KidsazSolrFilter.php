<?php

namespace LAZ\objects\tools\kidsaz;

use LAZ\objects\kidsaz\dataAccess\resources\ResourceDbGateway;
use LAZ\objects\library\businessObjects\kidsaz\BookroomCollection;

class KidsazSolrFilter {
    const PRIMARY_LANGUAGE_BOOST = 100;
    const TIE_BREAKER = 0.1;
    private $bookroomConfigs;
    private $studentLevelId;
    private $studentPrimaryLanguageId;
    private $resourceDbGateway;
    private $kidsazSolrCategoryFilter;
    private $kidsazSolrLanguageFilter;
    private $kidsazSolrLevelFilter;

    public function __construct(array $bookroomConfigs, int $studentLevelId,  int $studentPrimaryLanguageId) {
        $this->bookroomConfigs = $bookroomConfigs;
        $this->studentLevelId = $studentLevelId;
        $this->studentPrimaryLanguageId = $studentPrimaryLanguageId;
        $this->resourceDbGateway = new ResourceDbGateway();
        $this->kidsazSolrCategoryFilter = new KidsazSolrCategoryFilter();
        $this->kidsazSolrLanguageFilter = new KidsazSolrLanguageFilter();
        $this->kidsazSolrLevelFilter = new KidsazSolrLevelFilter($studentLevelId);
    }

    public function getAdditionalParameters() {
        $languageBoostString = SolrConstants::FIELD_LANGUAGE_ID . ":$this->studentPrimaryLanguageId^" . self::PRIMARY_LANGUAGE_BOOST;
        $languageBoostString = urlencode($languageBoostString);
        return  ['bq' => $languageBoostString, 'tie' => self::TIE_BREAKER];
    }

    public function getFilterQueryStringForBookroomCollections(array $bookroomCollections) {
        $collectionFilterStrings = [];
        $this->getFilterQueryStringForBookroomCollectionsHelper($bookroomCollections, $collectionFilterStrings);
        $filterStringForBookroomCollections = implode(" OR ", array_filter($collectionFilterStrings));
        return urlencode($filterStringForBookroomCollections);
    }

    private function getFilterQueryStringForBookroomCollectionsHelper(array $bookroomCollections, array &$collectionFilterStrings) {
        foreach ($bookroomCollections as $bookroomCollection) {
            $filterString = $this->getBookroomCollectionQueryString($bookroomCollection);
            $collectionFilterStrings[$bookroomCollection->getCollectionId()] = $filterString ?? "";
            if ($bookroomCollection->getChildCollections()) {
                $this->getFilterQueryStringForBookroomCollectionsHelper($bookroomCollection->getChildCollections(), $collectionFilterStrings);
            }
        }
        return;
    }

    private function getBookroomCollectionQueryString(BookroomCollection $bookroomCollection) {
        $collectionId = $bookroomCollection->getCollectionId();
        if ($this->bookroomConfigs[$collectionId]['is_enabled'] != "y") {
            return "";
        }
        $filterSubStrings = [];
        $filterSubStrings['levelRange'] = $this->kidsazSolrLevelFilter->getLevelRangeFilter($collectionId, $this->bookroomConfigs);
        $filterSubStrings['language'] = $this->kidsazSolrLanguageFilter->getLanguageFilter($collectionId, $this->bookroomConfigs);
        $filterSubStrings['category'] = $this->kidsazSolrCategoryFilter->getCategoryFilterForCollection($collectionId);
        if (!$filterSubStrings['category']) return ""; //only return filters with implemented categories
        $filterQueryString = implode(" AND ", array_filter($filterSubStrings));
        return "($filterQueryString)";
    }




}