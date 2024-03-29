<?php

/**
 * Jméno, Město Analyzer
 * Bachelor's Thesis
 * @author Václav Trampeška
 */

namespace App\Constants;

class QueryConstants
{
    const GRAPH_TYPE_KEY = 'graphType';
    const COUNT_TABLE_KEY = 'countTable';
    const COUNTRY_KEY = 'country';
    const CATEGORY_KEY = 'category';
    const LETTER_KEY = 'letter';
    const LANGUAGE_KEY = 'language';
    const LIMIT_KEY = 'limit';
    const PERCENTAGE_KEY = 'percentage';
    const WORD_KEY = 'word';
    const OPERATOR_KEY = 'operator';

    const POPULARITY_GRAPH = 'popular';
    const TOTAL_AMOUNT_GRAPH = 'total';
    const TIME_GRAPH = 'time';

    const COUNT_ANSWERS = 'answer';
    const COUNT_CATEGORIES = 'category';

    const OPERATOR_EQUALS = 'equals';
    const OPERATOR_STARTS_WITH = 'startsWith';
    const OPERATOR_ENDS_WITH = 'endsWith';
    const OPERATOR_CONTAINS = 'contains';

    const AMOUNT_COLUMN_NAME = 'amount';
    const TOTAL_ANSWERS_COLUMN_NAME = 'total';
    const ID_COLUMN_NAME = 'id';
    const CATEGORY_COLUMN_NAME = 'category';
    const ANSWER_COLUMN_NAME = 'answer';

    const WORD_TABLE_REST = 'word_rest';

    const ALL_LANGUAGES = 'all';

    const LIMIT_NUMBER = 200;
}
