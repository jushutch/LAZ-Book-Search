<?php

namespace LAZ\objects\kidsaz\services\bookroom;

use LAZ\objects\kidsaz\dataAccess\bookroom\RecommendedBooklistDbGateway;
use LAZ\objects\library\ActivityTypeHelper;

class RecommendedBooklistService {
    const MAX_BOOKS = 5;
    const GENRE_FIELD = "GenreID";
    const THEME_FIELD = "ThemeID";
    private $recommendedBooklistDbGateway;

    public function __construct($shardId) {
        $this->recommendedBooklistDbGateway = new RecommendedBooklistDbGateway($shardId);
    }

    public function getRecommendedBooklist($studentLevelId, $leveledCollectionConfig, $rkAccountId) {
        $completedResourceIds = $this->getCompletedReadResourceIds($rkAccountId);
        $levels = $this->getLeveledBookLevelsForStudent($studentLevelId, $leveledCollectionConfig);
        $topThemes = $this->getStudentTopThemes($completedResourceIds);
        $topThemeResourceIds = $this->getPopularResourceIdsForFieldAndLevels(self::THEME_FIELD, $topThemes, $levels, $completedResourceIds);
        if (sizeOf($topThemeResourceIds) < self::MAX_BOOKS) {
            $topGenres = $this->getStudentTopGenres($completedResourceIds);
            $topGenreResourceIds = $this->getPopularResourceIdsForFieldAndLevels(self::GENRE_FIELD, $topGenres, $levels, $completedResourceIds);
            return array_slice(array_merge($topThemeResourceIds, $topGenreResourceIds), 0, self::MAX_BOOKS);
        }
        return $topThemeResourceIds;
    }

    private function getStudentTopThemes($completedReadResourceIds) {
        return $this->recommendedBooklistDbGateway->getTopThemesOfResourceIds($completedReadResourceIds);
    }

    private function getStudentTopGenres($completedReadResourceIds) {
        return $this->recommendedBooklistDbGateway->getTopGenresOfResourceIds($completedReadResourceIds);
    }

    private function getCompletedReadResourceIds($rkAccountId) {
        return $this->recommendedBooklistDbGateway->getCompletedResourceIdsOfActivityTypeIdForStudentAccountId($rkAccountId, ActivityTypeHelper::READ_ACTIVITY_ID);

    }

    private function getPopularResourceIdsForFieldAndLevels(string $field, array $fieldIds, array $levelIds, array $completedResourceIds) {
        $booklist = [];
        foreach($fieldIds as $fieldId) {
            if (sizeOf($booklist) >= self::MAX_BOOKS) break;
            $resourceIds = $this->recommendedBooklistDbGateway->getPopularResourceIdsForThemeAndLevels($field, $fieldId, $levelIds, $completedResourceIds);
            foreach($resourceIds as $resourceId) {
                $booklist[] = $resourceId;
            }
        }
        return $booklist;
    }

    private function getLeveledBookLevelsForStudent($studentLevel, $leveledBookConfig) {
        if ($leveledBookConfig["is_enabled"] == 'n') return range($studentLevel, $studentLevel);
        if ($leveledBookConfig["float_enabled"] == 'y')  {
            $floatAboveLevel = $leveledBookConfig['float_above_level'];
            $floatBelowLevel = $leveledBookConfig['float_below_level'];
            $lowerReadingLevelBound = $studentLevel - $floatBelowLevel;
            $upperReadingLevelBound = $studentLevel + $floatAboveLevel;
            return range($lowerReadingLevelBound, $upperReadingLevelBound);
        }
        return range($leveledBookConfig['start_filter'], $leveledBookConfig['end_filter']);
    }

}