<?php

namespace LAZ\objects\tools\kidsaz;

use LAZ\objects\library\BookroomConstants;

class KidsazSolrCategoryFilter {

    public function getCategoryFilterForCollection(int $collectionId) {
        $nonfictionCollectionIdToCategoryId = array_flip(BookroomConstants::$CATEGORY_IDS_TO_NONFICTION_COLLECTION_IDS);
        $filterQueryString = "";
        switch($collectionId) {
            case BookroomConstants::LEVELED_BOOKS_COLLECTION_ID:
            case BookroomConstants::SPANISH_BOOKS_COLLECTION_ID:
            case BookroomConstants::POLISH_BOOKS_COLLECTION_ID:
            case BookroomConstants::UKRAINIAN_BOOKS_COLLECTION_ID:
            case BookroomConstants::VIETNAMESE_BOOKS_COLLECTION_ID:
            case BookroomConstants::BRITISH_BOOKS_COLLECTION_ID:
            case BookroomConstants::FRENCH_BOOKS_COLLECTION_ID:
                $filterQueryString .= $this->getFilterForCategory(BookroomConstants::LEVELED_BOOKS_CATEGORY_ID);
                break;
            case BookroomConstants::DECODABLE_BOOKS_COLLECTION_ID:
                $filterQueryString .= SolrConstants::FIELD_CATEGORY_ID . ":(" . BookroomConstants::DECODABLE_BOOKS_CATEGORY_ID .
                    " OR " . BookroomConstants::DECODABLE_PASSAGES_CATEGORY_ID . ")";
                break;
            case BookroomConstants::POETRY_BOOKS_COLLECTION_ID:
                $filterQueryString .= $this->getFilterForCategory(BookroomConstants::POETRY_BOOKS_CATEGORY_ID);
                break;
            case BookroomConstants::TRADE_BOOKS_COLLECTION_ID:
                $filterQueryString .= $this->getFilterForCategory(BookroomConstants::TRADE_BOOKS_CATEGORY_ID);
                break;
            case BookroomConstants::ALPHABET_BOOKS_COLLECTION_ID:
            case BookroomConstants::FRENCH_ALPHABET_BOOKS_COLLECTION_ID:
            case BookroomConstants::SPANISH_ALPHABET_BOOKS_COLLECTION_ID:
                $filterQueryString .= $this->getFilterForCategory(BookroomConstants::ALPHABET_BOOKS_CATEGORY_ID);
                break;
            case BookroomConstants::HIGH_FREQ_WORD_BOOKS_COLLECTION_ID:
            case BookroomConstants::SPANISH_HIGH_FREQ_WORD_BOOKS_COLLECTION_ID:
            case BookroomConstants::FRENCH_HIGH_FREQ_WORD_BOOKS_COLLECTION_ID:
                $filterQueryString .= $this->getFilterForCategory(BookroomConstants::HIGH_FREQ_WORD_BOOKS_CATEGORY_ID);
                break;
            case BookroomConstants::NURSERY_RHYMES_COLLECTION_ID:
                $filterQueryString .= $this->getFilterForCategory(BookroomConstants::NURSERY_RHYMES_CATEGORY_ID);
                break;
            case BookroomConstants::SONG_BOOKS_COLLECTION_ID:
                $filterQueryString .= $this->getFilterForCategory(BookroomConstants::SONG_BOOKS_CATEGORY_ID);
                break;
            case BookroomConstants::VOCAB_BOOKS_COLLECTION_ID:
                $filterQueryString .= SolrConstants::FIELD_CATEGORY_ID . ":(" . BookroomConstants::VOCAB_BOOKS_CATEGORY_ID .
                    " OR ". BookroomConstants::VOCAB_BOOKS_CHILD_CATEGORY_ID .
                    " OR " . BookroomConstants::IDIOM_BOOKS_CATEGORY_ID . ")";
                break;
            case BookroomConstants::SERIAL_BOOKS_COLLECTION_ID:
            case BookroomConstants::SPANISH_SERIES_BOOKS_COLLECTION_ID:
                $filterQueryString .= $this->getFilterForCategory(BookroomConstants::SERIAL_BOOKS_CATEGORY_ID);
                break;
            case isset(BookroomConstants::$SERIAL_BOOK_COLLECTION_IDS_TO_CATEGORY_IDS[$collectionId]) ? $collectionId : null:
                $categoryId = BookroomConstants::$SERIAL_BOOK_COLLECTION_IDS_TO_CATEGORY_IDS[$collectionId];
                $fictionSeriesId = BookroomConstants::$CATEGORY_IDS_TO_SERIAL_IDS[$categoryId];
                $filterQueryString .= SolrConstants::FIELD_FICTION_SERIES_ID . ":" . $fictionSeriesId;
                break;
            case isset($nonfictionCollectionIdToCategoryId[$collectionId]) ? $collectionId : null:
                $categoryId = $nonfictionCollectionIdToCategoryId[$collectionId];
                $nonfictionSeriesId = BookroomConstants::$CATEGORY_IDS_TO_NONFICTION_SERIES_IDS[$categoryId];
                $filterQueryString .= SolrConstants::FIELD_NONFICTION_SERIES_ID . ":" . $nonfictionSeriesId;
                break;
            case BookroomConstants::SOUND_SYMBOL_BOOKS_COLLECTION_ID:
                $filterQueryString .= $this->getFilterForCategory(BookroomConstants::SOUND_SYMBOL_CATEGORY_ID);
                break;
            case BookroomConstants::READ_ALOUD_BOOKS_COLLECTION_ID:
                $filterQueryString .= $this->getFilterForCategory(BookroomConstants::READ_ALOUD_CATEGORY_ID);
                break;
            case BookroomConstants::ELL_VOCAB_BOOKS_COLLECTION_ID:
                $filterQueryString .= $this->getFilterForCategory(BookroomConstants::ELL_VOCAB_CATEGORY_ID);
                break;
            case BookroomConstants::GRAPHIC_BOOKS_COLLECTION_ID:
                $filterQueryString .= SolrConstants::FIELD_CATEGORY_ID . ":(" . BookroomConstants::GRAPHIC_BOOKS_CATEGORY_ID .
                    " OR ". BookroomConstants::HUMOR_BOOKS_CATEGORY_ID .")";
                break;
            case BookroomConstants::SHARED_READING_BOOKS_COLLECTION_ID:
                $filterQueryString .=$this->getFilterForCategory(BookroomConstants::SHARED_READING_BOOKS_CATEGORY_ID);
                break;
            case BookroomConstants::SPANISH_SONGS_AND_RHYMES_COLLECTION_ID:
                $filterQueryString .= $this->getFilterForCategory(BookroomConstants::SPANISH_SONGS_AND_RHYMES_CATEGORY_ID);
                break;
            case BookroomConstants::CLASSICS_COLLECTION_ID:
                $filterQueryString .= SolrConstants::FIELD_CATEGORY_ID . ":(" . BookroomConstants::CLASSICS_CATEGORY_ID .
                    " OR ". BookroomConstants::SINGLE_BOOK_CLASSICS_CATEGORY_ID .
                    " OR ". BookroomConstants::MULTI_PART_CLASSICS_CATEGORY_ID . ")";
                break;
            case BookroomConstants::THEMED_NONFICTION_SERIES_COLLECTION_ID:
                $filterQueryString .= $this->getFilterForCategory(BookroomConstants::THEMED_NONFICTION_SERIES_CATEGORY_ID);
                break;
            case BookroomConstants::SPANISH_AUTHENTIC_BOOKS_COLLECTION_ID:
                $filterQueryString .= SolrConstants::FIELD_CATEGORY_ID . ":(" . BookroomConstants::SPANISH_AUTHENTIC_BOOKS_CATEGORY_ID .
                    " OR ". BookroomConstants::SPANISH_LIFE_IN_LATIN_AMERICA_CATEGORY_ID .
                    " OR ". BookroomConstants::SPANISH_BIOGRAPHIES_CATEGORY_ID .
                    " OR ". BookroomConstants::SPANISH_LEGENDS_AND_MYTHS_CATEGORY_ID . ")";
                break;
            case BookroomConstants::IGNITE_HIGH_LOW_GRAPHICS_COLLECTION_ID:
                $filterQueryString .= $this->getFilterForCategory(BookroomConstants::IGNITE_HIGH_LOW_GRAPHIC_CATEGORY_ID);
                break;
            case BookroomConstants::IGNITE_HIGH_LOW_TEXT_SETS_COLLECTION_ID:
                $filterQueryString .= $this->getFilterForNonbookCategory(BookroomConstants::HIGH_LOW_TEXT_SET_CATEGORY_ID);
                break;
            //non book collections
            case BookroomConstants::CLOSE_READING_PASSAGES_COLLECTION_ID:
                $filterQueryString .= $this->getFilterForNonbookCategory(BookroomConstants::CLOSE_READ_PASSAGE_NON_BOOK_CATEGORY_ID)
                    . " AND " . SolrConstants::FIELD_TITLE_LIST . ":\"Passage\"";
                break;
            case BookroomConstants::CLOSE_READING_PACKS_COLLECTION_ID:
                $filterQueryString .= $this->getFilterForNonbookCategory(BookroomConstants::CLOSE_READING_PACKS_NON_BOOK_CATEGORY_ID);
                break;
            case BookroomConstants::SPANISH_CLOSE_READING_PACKS_COLLECTION_ID:
                $filterQueryString .= $this->getFilterForNonbookCategory(BookroomConstants::SPANISH_CLOSE_READING_PACKS_NON_BOOK_CATEGORY_ID);
                break;
            case BookroomConstants::TEXT_SETS_COLLECTION_ID:
                $filterQueryString .= $this->getFilterForNonbookCategory(BookroomConstants::TEXT_SET_CATEGORY_ID);
                break;
            case BookroomConstants::PROJECT_BASED_LEARNING_PACKS_COLLECTION_ID:
                $filterQueryString .= $this->getFilterForNonbookCategory(BookroomConstants::PROJECT_BASED_LEARNING_PACKS_CATEGORY_ID);
                break;
            case BookroomConstants::ELL_COMIC_CONVERSATIONS_ID:
                $filterQueryString .= $this->getFilterForNonbookCategory(BookroomConstants::ELL_COMIC_CONVERSATIONS_NON_BOOK_CATEGORY_ID);
                break;
            case BookroomConstants::ELL_GRAMMAR_RESOURCES_COLLECTION_ID:
                $filterQueryString .= $this->getFilterForNonbookCategory(BookroomConstants::ELL_GRAMMAR_RESOURCES_NON_BOOK_CATEGORY_ID)
                    . " AND " . SolrConstants::FIELD_TITLE_LIST . ":\"Grammar Card and Guide\"";
                break;
            case BookroomConstants::ELL_VOCAB_POWER_PACKS_COLLECTION_ID:
                $filterQueryString .= $this->getFilterForNonbookCategory(BookroomConstants::ELL_VOCABULARY_POWER_PACKS_NON_BOOK_CATEGORY_ID)
                    . " AND " . SolrConstants::FIELD_SORTABLE_TITLE . ":\"Student Cards\"";
                break;
            default:
                return "";
        }
        return $filterQueryString;
    }

    private function getFilterForCategory(int $categoryId) {
        return SolrConstants::FIELD_CATEGORY_ID . ":" . $categoryId;
    }

    private function getFilterForNonbookCategory(int $categoryId) {
        return "(". SolrConstants::FIELD_NONBOOK_CATEGORY_ID . ":" . $categoryId . " OR " .
            SolrConstants::FIELD_PACK_CATEGORY_ID . ":" . $categoryId . ")";
    }
}