<?php

namespace LAZ\objects\tests\objects\tools;

use LAZ\objects\library\BookroomConstants;
use LAZ\objects\library\businessObjects\kidsaz\BookroomCollection;

class KidsazSearchSolrMockDataService {
    private $mockCollectionConfigs;
    private $kidsazSearchSolrMockDataDbGateway;

    public function __construct($testLevels) {
        $this->kidsazSearchSolrMockDataDbGateway = new KidsazSearchSolrMockDataDbGateway();
        $this->mockCollectionConfigs = $this->buildMockCollectionConfigs($testLevels);
    }

    static $RK_BOOKROOM_COLLECTIONS = [
        BookroomConstants::CARLOS_COLLECTION_ID,
        BookroomConstants::MARIA_COLLECTION_ID,
        BookroomConstants::THE_HOPPERS_COLLECTION_ID,
        BookroomConstants::GREAT_GALLARDOS_BOOKS_COLLECTION_ID,
        BookroomConstants::MORTY_MOUSE_COLLECTION_ID,
        BookroomConstants::THE_HOLLOW_KIDS_COLLECTION_ID,
        BookroomConstants::FUNNY_PHONICS_COLLECTION_ID,
        BookroomConstants::MONEYBAGS_MIKE_COLLECTION_ID,
        BookroomConstants::NURSERY_RHYMES_COLLECTION_ID,
        BookroomConstants::POETRY_BOOKS_COLLECTION_ID,
        BookroomConstants::SONG_BOOKS_COLLECTION_ID,
        BookroomConstants::LEVELED_BOOKS_COLLECTION_ID,
        BookroomConstants::SPANISH_BOOKS_COLLECTION_ID
    ];

    static $RAZ_BOOKROOM_COLLECTIONS = [
        BookroomConstants::SPANISH_BOOKS_COLLECTION_ID,
        BookroomConstants::POETRY_BOOKS_COLLECTION_ID,
        BookroomConstants::LEVELED_BOOKS_COLLECTION_ID,
        BookroomConstants::CLASSICS_COLLECTION_ID,
        BookroomConstants::TRADE_BOOKS_COLLECTION_ID,
        BookroomConstants::GRAPHIC_BOOKS_COLLECTION_ID,
        BookroomConstants::BRILLIANT_BIOMES_COLLECTION_ID,
        BookroomConstants::COLORS_COLLECTION_ID,
        BookroomConstants::COUNTRIES_AROUND_THE_WORLD_COLLECTION_ID,
        BookroomConstants::GIANTS_OF_THE_ANIMAL_WORLD_COLLECTION_ID,
        BookroomConstants::MY_BODY_COLLECTION_ID,
        BookroomConstants::NATIONAL_PARKS_COLLECTION_ID,
        BookroomConstants::NUMBERS_COLLECTION_ID,
        BookroomConstants::SPECTACULAR_SPORTS_COLLECTION_ID,
        BookroomConstants::TRIP_ON_A_LATITUDE_LINE_COLLECTION_ID,
        BookroomConstants::US_GOVERNMENT_COLLECTION_ID,
        BookroomConstants::WORLD_LANDMARKS_COLLECTION_ID,
        BookroomConstants::WORLD_LEADERS_COLLECTION_ID,
        BookroomConstants::ALPHABET_BOOKS_COLLECTION_ID,
        BookroomConstants::HIGH_FREQ_WORD_BOOKS_COLLECTION_ID,
        BookroomConstants::SHARED_READING_BOOKS_COLLECTION_ID,
        BookroomConstants::VOCAB_BOOKS_COLLECTION_ID,
        BookroomConstants::ELL_GRAMMAR_RESOURCES_COLLECTION_ID,
        BookroomConstants::ELL_VOCAB_BOOKS_COLLECTION_ID,
        BookroomConstants::SPANISH_ALPHABET_BOOKS_COLLECTION_ID,
        BookroomConstants::SPANISH_HIGH_FREQ_WORD_BOOKS_COLLECTION_ID,
        BookroomConstants::SPANISH_SERIES_BOOKS_COLLECTION_ID,
        BookroomConstants::NURSERY_RHYMES_COLLECTION_ID,
        BookroomConstants::IGNITE_HIGH_LOW_GRAPHICS_COLLECTION_ID,

        // Failing Tests
//        BookroomConstants::CLOSE_READING_PASSAGES_COLLECTION_ID,
//        BookroomConstants::CLOSE_READING_PACKS_COLLECTION_ID,
//        BookroomConstants::PROJECT_BASED_LEARNING_PACKS_COLLECTION_ID,
//        BookroomConstants::DECODABLE_BOOKS_COLLECTION_ID,
//        BookroomConstants::READ_ALOUD_BOOKS_COLLECTION_ID,
//        BookroomConstants::ELL_COMIC_CONVERSATIONS_ID,
//        BookroomConstants::SPANISH_CLOSE_READING_PACKS_COLLECTION_ID,
//        BookroomConstants::SPANISH_SONGS_AND_RHYMES_COLLECTION_ID,
//        BookroomConstants::TEXT_SETS_COLLECTION_ID,
//        BookroomConstants::IGNITE_HIGH_LOW_TEXT_SETS_COLLECTION_ID

    ];

    private function buildMockCollectionConfigs(array $testLevels) {
        $mockCollectionConfigs = [];
        foreach ($this->kidsazSearchSolrMockDataDbGateway->getBookroomCollections($this->getAllCollectionIds()) as $collection) {
            $testLevelId = $testLevels[$collection["filter_type"]];
            $collectionConfig = [
                'collection_id' => $collection["bookroom_collection_id"],
                'filter_type' => $collection["filter_type"],
                'language_id' => $collection["language_id"],
                'start_filter' => $testLevelId,
                'end_filter' => $testLevelId,
                'is_enabled' => $collection["is_active"]
            ];
            $mockCollectionConfigs[$collection["bookroom_collection_id"]] = $collectionConfig;
        }
        return $mockCollectionConfigs;
    }

    public function getMockCollectionConfigs() {
        return $this->mockCollectionConfigs;
    }

    public static function getMockBookroomCollectionForCollectionId($collectionId) {
        return new BookroomCollection(
            $collectionId,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null);
    }

    public function getCollectionIdsForSubscription(bool $hasRazPlus) {
        return $hasRazPlus ? array_merge(self::$RK_BOOKROOM_COLLECTIONS, self::$RAZ_BOOKROOM_COLLECTIONS) : self::$RK_BOOKROOM_COLLECTIONS;
    }

    public function getAllCollectionIds() {
        return array_merge(self::$RK_BOOKROOM_COLLECTIONS, self::$RAZ_BOOKROOM_COLLECTIONS);
    }

    public function getResourceIdForNonBook($resourceId) {
        return $this->kidsazSearchSolrMockDataDbGateway->getResourceIdForNonBook($resourceId);
    }

}