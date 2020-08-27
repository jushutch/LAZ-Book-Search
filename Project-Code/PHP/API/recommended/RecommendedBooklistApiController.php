<?php

namespace LAZ\objects\kidsaz\api\RecommendedBooklist;

use LAZ\objects\kidsaz\services\bookroom\RecommendedBooklistService;
use LAZ\objects\kidsaz\services\StudentResourceService;
use LAZ\objects\library\BookroomConstants;
use LAZ\objects\razkids\StudentInfoCache;
use LAZ\objects\razkids\TeacherInfoCache;
use Psr\Http\Message\ServerRequestInterface;

class RecommendedBooklistApiController {
    private $config;
    private $recommendedBooklistService;

    public function __construct() {
        $this->shardId = TeacherInfoCache::getShardConfigurationId();
        $this->config = StudentInfoCache::getReadingResourceDeliveryConfig();
        $this->recommendedBooklistService = new RecommendedBooklistService($this->shardId);
    }

    public function getRecommendedBooklist(ServerRequestInterface $request) {
        $studentLevelId = StudentInfoCache::getLevelId();
        $leveledCollectionConfig = StudentInfoCache::getBookroomConfigs()[BookroomConstants::LEVELED_BOOKS_COLLECTION_ID];
        $rkAccountId = StudentInfoCache::getReadingAccountId();
        $resourceIds = $this->recommendedBooklistService->getRecommendedBooklist($studentLevelId, $leveledCollectionConfig, $rkAccountId);
        return array_values(StudentResourceService::getResourcesWithDeliveriesWithStatuses($resourceIds, $this->config));
    }
}