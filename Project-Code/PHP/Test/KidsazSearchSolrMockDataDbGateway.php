<?php

namespace LAZ\objects\tests\objects\tools;

use LAZ\objects\data\DataManager;
use LAZ\objects\library\SQLUtil;
use LAZ\objects\tools\utils\IdValidator;

class KidsazSearchSolrMockDataDbGateway {

    private $rkContentWebDm;

    public function __construct() {
        $this->rkContentWebDm = new DataManager(DataManager::DB_RK_CONTENT, DataManager::LOC_WEB);
    }

    public function getBookroomCollections(array $collectionIds) {
        $collectionIds = SQLUtil::csvStringFromIterable($collectionIds);
        $sql = "SELECT bookroom_collection_id, language_id, filter_type, is_active, is_raz_required
                FROM rk_content.bookroom_collection
                WHERE bookroom_collection_id in ($collectionIds)";
        $this->rkContentWebDm->query($sql);
        $bookroomCollections = [];
        foreach ($this->rkContentWebDm->fetchAll() as $row) {
            $bookroomCollections[$row["bookroom_collection_id"]] = $row;
        }
        return $bookroomCollections;
    }

    public function getResourceIdForNonBook($razNonBookId) {
        IdValidator::validateId($razNonBookId);
        $sql = "SELECT id
                FROM rk_content.rk_resources
                WHERE raz_non_book_id = $razNonBookId
                AND is_deleted = 0";
        $this->rkContentWebDm->query($sql);
        return $this->rkContentWebDm->fetch()["id"];
    }

}