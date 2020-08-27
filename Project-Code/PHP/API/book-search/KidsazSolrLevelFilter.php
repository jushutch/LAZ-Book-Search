<?php

namespace LAZ\objects\tools\kidsaz;

use LAZ\objects\library\BookroomConstants;
use LAZ\objects\razkids\RKConstants;
use LAZ\objects\kidsaz\dataAccess\resources\ResourceDbGateway;

class KidsazSolrLevelFilter {
    private $studentLevelId;
    private $resourceDbGateway;

    public function __construct(int $studentLevelId) {
        $this->studentLevelId = $studentLevelId;
        $this->resourceDbGateway = new ResourceDbGateway();
    }

    public function getLevelRangeFilter(int $collectionId, array $bookroomConfigs) {
        $filterType = $bookroomConfigs[$collectionId]['filter_type'];
        switch($filterType) {
            case BookroomConstants::NO_FILTER_TYPE:
                return "";
            case BookroomConstants::LEVEL_ID_FILTER_TYPE:
                return $this->getLevelIdFilterString($collectionId, $bookroomConfigs);
            case BookroomConstants::ALPHA_FILTER_TYPE:
                return $this->getAlphaFilterString($collectionId, $bookroomConfigs);
            case BookroomConstants::LANGUAGE_LEVEL_FILTER_TYPE:
                return $this->getLanguageLevelFilterString($collectionId, $bookroomConfigs);
            case BookroomConstants::INTEREST_LEVEL_FILTER_TYPE:
                return $this->getInterestLevelFilterString($collectionId, $bookroomConfigs);
            default:
                throw new \Exception("Bookroom collection uses an invalid filter type");
        }
    }

    private function getLevelIdFilterString(int $collectionId, array $bookroomConfigs) {
        $startFilter = $bookroomConfigs[$collectionId]['start_filter'];
        $endFilter = $bookroomConfigs[$collectionId]['end_filter'];
        $levelRange = range($startFilter, $endFilter);
        switch($collectionId) {
            case BookroomConstants::TEXT_SETS_COLLECTION_ID:
            case BookroomConstants::PROJECT_BASED_LEARNING_PACKS_COLLECTION_ID:
                return "(" . $this->getLevelFilterByFieldWithWildcards($levelRange, SolrConstants::FIELD_GRADE)
                    . " OR " . $this->getLevelFilterByFieldWithWildcards($levelRange, SolrConstants::FIELD_GRADE_RANGE)
                    . ")";
            case BookroomConstants::ARGUMENTATION_SKILL_PACKS_COLLECTION_ID:
            case BookroomConstants::TRADE_BOOKS_COLLECTION_ID:
                return $this->getLevelFilterByFieldWithoutWildcards($levelRange, SolrConstants::FIELD_LEXILE_LEVEL);
            case BookroomConstants::SHARED_READING_BOOKS_COLLECTION_ID:
                return $this->getLevelFilterByFieldWithoutWildcards($levelRange, SolrConstants::FIELD_SHARED_READING_LEVEL);
            case BookroomConstants::SPANISH_CLOSE_READING_PACKS_COLLECTION_ID:
            case BookroomConstants::CLOSE_READING_PACKS_COLLECTION_ID:
            case BookroomConstants::ELL_COMIC_CONVERSATIONS_ID:
                return $this->getLevelFilterByFieldWithWildcards($levelRange, SolrConstants::FIELD_GRADE_RANGE);
            case BookroomConstants::CLOSE_READING_PASSAGES_COLLECTION_ID:
                return $this->getLevelFilterByFieldWithWildcards($levelRange, SolrConstants::FIELD_PARENT_NONBOOK_CATEGORY);
            default:
                return null;
        }
    }

    private function getLevelFilterByFieldWithWildcards(array $levels, string $field) {
        $filterString = "$field:(";
        foreach($levels as $level) {
            $filterString .= "*$level* OR ";
        }
        return $this->chopOffLastConnector($filterString) . ")";
    }

    private function getLevelFilterByFieldWithoutWildcards(array $levels, string $field) {
        $filterString = "$field:(";
        foreach($levels as $level) {
            $filterString .= "$level OR ";
        }
        return $this->chopOffLastConnector($filterString) . ")";
    }

    private function getSolrRangeFilter(int $start, int $end, string $field) {
        return "$field:[$start TO $end]";
    }

    private function getLanguageLevelFilterString(int $collectionId, array $bookroomConfigs) {
        $startFilter = $bookroomConfigs[$collectionId]['start_filter'];
        $endFilter = $bookroomConfigs[$collectionId]['end_filter'];
        $levels = $this->getEllLanguageLevelFromConfig($startFilter, $endFilter);
        $languageLevelFilterSubstrings = [];
        $languageLevelFilterSubstrings["languageLevel"] = $levels["languageLevelIdList"]
            ? $this->getLevelFilterByFieldWithoutWildcards($levels["languageLevelIdList"], SolrConstants::FIELD_ELL_LANGUAGE_LEVEL)
            : null;
        $languageLevelFilterSubstrings["gradeLevel"] = $levels["gradeLevelList"]
            ? $this->getLevelFilterByFieldWithWildcards($levels["gradeLevelList"], SolrConstants::FIELD_GRADE_RANGE)
            : null;
        $filterString = implode(" AND ", array_filter($languageLevelFilterSubstrings));
        return $filterString ? "($filterString)" : null;
    }

    private function getEllLanguageLevelFromConfig($languageLevelStart, $languageLevelEnd) {
        $gradeLevelList = [];
        $languageLevelIdList = [];
        if($languageLevelStart == BookroomConstants::LOWER_GRADE_RANGE_GRAMMAR_LOWER_LANGUAGE_LEVEL_ID
            && $languageLevelEnd == BookroomConstants::LOWER_GRADE_RANGE_GRAMMAR_UPPER_LANGUAGE_LEVEL_ID) {
            $gradeLevelList = range(1, 2);
            $languageLevelIdList = null;
        } elseif($languageLevelStart == BookroomConstants::UPPER_GRADE_RANGE_LOWER_LANGUAGE_LEVEL_ID
            && $languageLevelEnd == BookroomConstants::UPPER_GRADE_RANGE_UPPER_LANGUAGE_LEVEL_ID) {
            $gradeLevelList = range(3, 5);
            $languageLevelIdList = null;
        } elseif($languageLevelStart == BookroomConstants::LOWER_GRADE_RANGE_GRAMMAR_LOWER_LANGUAGE_LEVEL_ID
            && $languageLevelEnd == BookroomConstants::LOWER_GRADE_RANGE_GRAMMAR_LOWER_LANGUAGE_LEVEL_ID) {
            $gradeLevelList = range(1, 2);
            $languageLevelIdList = range(1, 2);
        } elseif($languageLevelStart == BookroomConstants::LOWER_GRADE_RANGE_GRAMMAR_UPPER_LANGUAGE_LEVEL_ID
            && $languageLevelEnd == BookroomConstants::LOWER_GRADE_RANGE_GRAMMAR_UPPER_LANGUAGE_LEVEL_ID) {
            $gradeLevelList = range(1, 2);
            $languageLevelIdList = range(3, 5);
        } elseif($languageLevelStart == BookroomConstants::UPPER_GRADE_RANGE_LOWER_LANGUAGE_LEVEL_ID
            && $languageLevelEnd == BookroomConstants::UPPER_GRADE_RANGE_LOWER_LANGUAGE_LEVEL_ID) {
            $gradeLevelList = range(3, 5);
            $languageLevelIdList = range(1, 2);
        } elseif($languageLevelStart == BookroomConstants::UPPER_GRADE_RANGE_UPPER_LANGUAGE_LEVEL_ID
            && $languageLevelEnd == BookroomConstants::UPPER_GRADE_RANGE_UPPER_LANGUAGE_LEVEL_ID) {
            $gradeLevelList = range(3, 5);
            $languageLevelIdList = range(3, 5);
        }

        return [ "gradeLevelList" => $gradeLevelList,
            "languageLevelIdList" => $languageLevelIdList
        ];
    }

    private function getInterestLevelFilterString(int $collectionId, array $bookroomConfigs) {
        $startFilter = $bookroomConfigs[$collectionId]['start_filter'];
        $endFilter = $bookroomConfigs[$collectionId]['end_filter'];
        $filterString = SolrConstants::FIELD_LEVEL . ":(";
        foreach(range($startFilter, $endFilter) as $readingLevel) {
            $filterString .= "$readingLevel* OR ";
        }
        return $this->chopOffLastConnector($filterString) . ")";
    }

    private function getAlphaFilterString(int $collectionId, array $bookroomConfigs) {
        $collectionConfig = $bookroomConfigs[$collectionId];
        $isSpanishLevelCollection = $collectionConfig["language_id"] == RKConstants::SPANISH_LANGUAGE_ID;
        $readingLevelId = $isSpanishLevelCollection
            ? $this->convertReadingLevelId($this->studentLevelId)
            : $this->studentLevelId;
        if ($this->isReadingRoomLevelFloatEnabled($collectionConfig))  {
            return $this->getFloatFilterString($collectionConfig, $readingLevelId, $isSpanishLevelCollection);
        }
        $startFilter = $isSpanishLevelCollection
            ? $this->convertReadingLevelId($collectionConfig['start_filter'])
            : $collectionConfig['start_filter'];
        $endFilter = $isSpanishLevelCollection
            ? $this->convertReadingLevelId($collectionConfig['end_filter'])
            : $collectionConfig['end_filter'];
        return $this->getSolrRangeFilter(
            $startFilter,
            $endFilter,
            ($isSpanishLevelCollection ? SolrConstants::FIELD_SPANISH_LEVEL_ID : SolrConstants::FIELD_LEVEL_ID)
        );
    }

    private function getFloatFilterString(array $collectionConfig, int $readingLevelId, bool $isSpanishLevelCollection) {
        $floatAboveLevel = $collectionConfig['float_above_level'];
        $floatBelowLevel = $collectionConfig['float_below_level'];
        $lowerReadingLevelBound = $readingLevelId - $floatBelowLevel;
        $upperReadingLevelBound = $readingLevelId + $floatAboveLevel;
        return $this->getSolrRangeFilter(
            $lowerReadingLevelBound,
            $upperReadingLevelBound,
            ($isSpanishLevelCollection ? SolrConstants::FIELD_SPANISH_LEVEL_ID : SolrConstants::FIELD_LEVEL_ID)
        );
    }

    private function isReadingRoomLevelFloatEnabled(array $collectionConfig) {
        return $collectionConfig['float_enabled'] == 'y';
    }

    private function convertReadingLevelId(int $levelId) {
        return $this->resourceDbGateway->convertReadingLevelId($levelId);
    }

    private function chopOffLastConnector(string $string) {
        return preg_replace("/ OR $/", "", $string);
    }
}