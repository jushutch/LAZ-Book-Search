<?php

namespace LAZ\objects\kidsaz\api\BookSearch;

use LAZ\objects\kidsaz\services\bookroom\BookroomCollectionService;
use LAZ\objects\kidsaz\services\resource\ResourceSearchService;
use LAZ\objects\library\SubscriptionHelper;
use LAZ\objects\razkids\StudentInfoCache;
use LAZ\objects\razkids\TeacherInfoCache;
use Psr\Http\Message\ServerRequestInterface;

class BookSearchApiController {
    private $resourceSearchService;
    private $bookroomCollectionService;

    public function __construct() {
        $this->bookroomCollectionService = BookroomCollectionService::instance(TeacherInfoCache::getShardConfigurationId());
        $this->resourceSearchService = new ResourceSearchService(
            $this->getAvailableBookroomCollections(),
            StudentInfoCache::getBookroomConfigs(),
            StudentInfoCache::getLevelId(),
            StudentInfoCache::getLeveledBookLanguageId(),
            StudentInfoCache::getReadingResourceDeliveryConfig()
        );
    }

    public function searchBooks(ServerRequestInterface $request) {
        $searchText = $request->getQueryParams()['search'];
        return $this->resourceSearchService->getResourcesBySearchTerms(
            $searchText, StudentInfoCache::getLevelId(),
            SubscriptionHelper::hasRazPlusAuthorization()
        );
    }

    private function getAvailableBookroomCollections() {
        return $this->bookroomCollectionService->getActiveBookroomCollectionsForStudent(
                StudentInfoCache::getStudentId(),
                studentInfoCache::getSubscriptionAccount(),
                studentInfoCache::getClassroomId()
            );
    }
}