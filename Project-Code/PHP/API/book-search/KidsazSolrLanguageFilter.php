<?php

namespace LAZ\objects\tools\kidsaz;

class KidsazSolrLanguageFilter {

    public function getLanguageFilter(int $collectionId, array $bookroomConfigs) {
        $languageId = $bookroomConfigs[$collectionId]["language_id"];
        return SolrConstants::FIELD_LANGUAGE_ID . ":" . $languageId;
    }

}