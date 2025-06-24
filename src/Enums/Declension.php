<?php

namespace UkrainianDeclension\Enums;

/**
 * Enum representing the four declension groups for Ukrainian nouns.
 */
enum Declension: int
{
    case FIRST = 1;
    case SECOND = 2;
    case THIRD = 3;
    case FOURTH = 4;
    case INDECLINABLE = 5;
} 