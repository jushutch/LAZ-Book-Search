<?php

namespace LAZ\objects\kidsaz\services\resource;

use LAZ\objects\kidsaz\businessObjects\StudentResourceDeliveryConfig;
use LAZ\objects\kidsaz\dataAccess\resources\ResourceDbGateway;
use LAZ\objects\kidsaz\services\StudentResourceService;
use LAZ\objects\tools\kidsaz\KidsazSearchSolr;

class ResourceSearchService {
    private $resourceDeliveryConfig;
    private $resourceDbGateway;
    private $kidsazSearchSolr;
    private $availableBookroomCollections;

    const MAX_SEARCH_RESULTS = 5;

    public function __construct(
        array $availableBookroomCollections,
        array $bookroomConfigs,
        int $levelId,
        int $languageId,
        StudentResourceDeliveryConfig $resourceDeliveryConfig)
    {
        $this->resourceDeliveryConfig = $resourceDeliveryConfig;
        $this->resourceDbGateway = new ResourceDbGateway();
        $this->availableBookroomCollections = $availableBookroomCollections;
        $this->kidsazSearchSolr = new KidsazSearchSolr(
            $bookroomConfigs,
            $levelId,
            $languageId
        );
    }

    public function getResourcesBySearchTerms($searchText, $studentLevelId, $hasRazPlus) {
        if (empty($searchText)) return [];
        $searchResults = $this->kidsazSearchSolr->getSearchResults(
            $searchText,
            $this->availableBookroomCollections,
            $hasRazPlus
        );
        $searchResults = $this->combineMultiLevelSearchResults($searchResults, $studentLevelId);
        $searchResults = $this->sortResultsByScore($searchResults);
        $resources = array_values($this->getResourcesFromSearchResults($searchResults));
        return $this->moveExactTitleMatchToTop($resources, $searchText);
    }

    private function getResourcesFromSearchResults($searchResults) {
        $resourceIds = $this->getResourceIdsFromSearchResults($searchResults);
        return $this->orderResources($resourceIds, StudentResourceService::getResourcesWithDeliveriesWithStatuses($resourceIds, $this->resourceDeliveryConfig));
    }

    private function combineMultiLevelSearchResults($searchResults, $studentLevelId) {
        $combinedSearchResults = [];
        $multiLevelBooks = [];
        foreach($searchResults as $result) {
            if (isset($result['multiLevelBookStatus']) && $result['multiLevelBookStatus']) {
                $multiLevelParentId = $this->resourceDbGateway->getPrimaryBookIdForMultiLevelBook($result['bookId']);
                $result['multiLevelParentId'] = $multiLevelParentId ?? $result['bookId'];
                if ($this->isClosestToReadingLevelAndValid($multiLevelBooks, $result, $studentLevelId)) {
                    $multiLevelBooks[$result['languageId']][$result['multiLevelParentId']] = $result;
                }
            } else {
                $combinedSearchResults[] = $result;
            }
        }
        $multiLevelBooks = $this->collapseMultilevelArray($multiLevelBooks);
        return array_merge($combinedSearchResults, $multiLevelBooks);
    }

    private function collapseMultilevelArray(array $multiLevelBooks) {
        $books = [];
        foreach ($multiLevelBooks as $booksForLanguage) {
            foreach ($booksForLanguage as $bookForLanguage) {
                $books[] = $bookForLanguage;
            }
        }
        return $books;
    }

    private function sortResultsByScore(array $searchResults) {
        usort($searchResults, function($a, $b) { return $b["score"] <=> $a["score"]; });
        return $searchResults;
    }

    private function isClosestToReadingLevelAndValid($multiLevelBooks, $result, $studentLevelId) {
        return !isset($multiLevelBooks[$result['languageId']][$result['multiLevelParentId']])
            || ($multiLevelBooks[$result['languageId']][$result['multiLevelParentId']] === $result['multiLevelParentId']
            && ($this->levelIdDifference($multiLevelBooks[$result['languageId']][$result['multiLevelParentId']]['levelId'], $studentLevelId) > $this->levelIdDifference($result['levelId'], $studentLevelId)));
    }

    private function levelIdDifference($levelId1, $levelId2) {
        return abs($levelId1 - $levelId2);
    }

    private function getResourceIdsFromSearchResults($searchResults) {
        $resourceIds = [];
        foreach($searchResults as $result) {
            if(sizeof($resourceIds) >= self::MAX_SEARCH_RESULTS) break;
            $resultResourceId = $this->getResultResourceId($result);
            if(isset($resultResourceId)) $resourceIds[] = $resultResourceId;
        }
        return $resourceIds;
    }

    private function getResultResourceId($result) {
        if($result['isNonbook']) {
            $nonbookResource = $this->resourceDbGateway->getResourceIdForNonBook($result['nonBookId']);
            if ($nonbookResource) {
                return (int)$nonbookResource['id'];
            }
            return null;
        }
        return $result['rkBookId'];
    }

    private function orderResources(array $resourceIds, array $resources) {
        $orderedResources = [];
        foreach($resourceIds as $resourceId) {
            $orderedResources[$resourceId] = $resources[$resourceId];
        }
        return $orderedResources;
    }

    private function moveExactTitleMatchToTop(array $resources, string $searchText) {
        $lowerCaseSearchText = strtolower($searchText);
        for ($i = 0; $i < count($resources); ++$i) {
            $lowercaseTitle = strtolower($resources[$i]->title);
            if ($lowerCaseSearchText === $lowercaseTitle) {
                $tempResource = $resources[$i];
                unset($resources[$i]);
                array_unshift($resources, $tempResource);
                return $resources;
            }
        }
        return $resources;
    }

}