<?php

namespace LAZ\objects\tests\objects\tools;

use LAZ\objects\kidsaz\services\bookroom\BookroomCollectionService;
use LAZ\objects\kidsaz\services\bookroom\BookroomCollectionServiceImpl;
use LAZ\objects\library\BookroomConstants;
use LAZ\objects\library\FrontContentLists;
use LAZ\objects\tools\kidsaz\KidsazSearchSolr;
use PHPUnit\Framework\TestCase;

class KidsazSearchSolrTest extends TestCase {
    static $TEST_LEVELS = [
        BookroomConstants::LEVEL_ID_FILTER_TYPE => 3,
        BookroomConstants::ALPHA_FILTER_TYPE => 14,
        BookroomConstants::NO_FILTER_TYPE=> 14,
        BookroomConstants::LANGUAGE_LEVEL_FILTER_TYPE => 12
    ];
    private $bookroomCollectionService;
    private $kidsazSearchSolr;
    private $levelHash;
    private $bookroomConfig;
    private $mockDataService;

    protected function setUp() {
        parent::setUp();
        $this->mockDataService = new KidsazSearchSolrMockDataService(self::$TEST_LEVELS);
        $this->bookroomConfig = $this->mockDataService->getMockCollectionConfigs();
        $this->bookroomCollectionService = BookroomCollectionService::instance(1);
        $this->kidsazSearchSolr = new KidsazSearchSolr(
            $this->bookroomConfig,
            self::$TEST_LEVELS[BookroomConstants::ALPHA_FILTER_TYPE],
            1
        );
        $this->levelHash = FrontContentLists::getLevelNameHashById();
    }

    /** @test */
    public function test_all_bookroom_collections() {
        foreach($this->mockDataService->getCollectionIdsForSubscription(true) as $collectionId) {
            $this->collectionTestHelper($collectionId, true);
        }
        foreach($this->mockDataService->getCollectionIdsForSubscription(false) as $rkCollectionId) {
            $this->collectionTestHelper($rkCollectionId, false);
        }
    }

    private function collectionTestHelper($collectionId, $hasRazAccess) {
        $bookroomCollectionServiceImpl = BookroomCollectionServiceImpl::instance(1);
        $levelToTest = $this->getTestLevelForCollection($collectionId);
        $collectionBooklist = $bookroomCollectionServiceImpl->getCollectionBooklist(
            $collectionId,
            $hasRazAccess,
            $levelToTest,
            null,
            null
        );
        $collectionBooklistCount = count($collectionBooklist);
        $collectionBookMap = $this->bookMapFromArray("kids_book_id", $collectionBooklist);

        $mockBookroomCollection = KidsazSearchSolrMockDataService::getMockBookroomCollectionForCollectionId(
            $collectionId
        );

        $searchResults = $this->kidsazSearchSolr->getSearchResults("*:*", [$mockBookroomCollection], $hasRazAccess);
        $searchResultsMap = $this->bookMapFromArray("rkBookId", $searchResults);
        $searchBooklistCount = count($searchResults);
        //$this->getDifferenceInBooklists($collectionBookMap, $searchResultsMap);
        $this->assertCount($collectionBooklistCount, $searchResults, ($hasRazAccess ? "RAZ " : "RK ") . "collection id $collectionId failed: $searchBooklistCount results, should be $collectionBooklistCount");
        $this->getDifferenceInBooklists($collectionBookMap, $searchResultsMap);
    }

    private function getTestLevelForCollection($collectionId) {
        switch($this->bookroomConfig[$collectionId]["filter_type"]) {
            case BookroomConstants::ALPHA_FILTER_TYPE:
                return $this->levelHash[self::$TEST_LEVELS[BookroomConstants::ALPHA_FILTER_TYPE]];
            case BookroomConstants::LEVEL_ID_FILTER_TYPE:
                return self::$TEST_LEVELS[BookroomConstants::LEVEL_ID_FILTER_TYPE];
            case BookroomConstants::LANGUAGE_LEVEL_FILTER_TYPE:
                return "1-2-12-12";
            default:
                return null;
        }
    }

    private function bookMapFromArray($idField, array $booklist) {
        $map = [];
        foreach ($booklist as $book) {
            if (isset($book[$idField])) {
                $map[$book[$idField]] = $book;
            } else {
                $resourceId = $this->mockDataService->getResourceIdForNonBook($book["nonBookId"]);
                $map[$resourceId] = $book;
            }
        }
        return $map;
    }

    private function getDifferenceInBooklists(array $booklist1, array $booklist2) {
        foreach ($booklist1 as $bookId => $book) {
            if (isset($booklist2[$bookId])) {
                unset($booklist1[$bookId]);
                unset($booklist2[$bookId]);
            }
        }
        $this->assertSameSize($booklist1, $booklist2, "The two booklists are not identical");
    }

}