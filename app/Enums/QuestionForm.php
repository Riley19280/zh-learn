<?php

namespace App\Enums;

enum QuestionForm: string {
    case Chinese = 'chinese';
    case Pinyin = 'pinyin';
    case English = 'english';
    case Audio = 'audio';

    public function label(): string {
        return match ($this) {
            self::Chinese => 'Chinese',
            self::Pinyin => 'Pinyin',
            self::English => 'English',
            self::Audio => 'Audio',
        };
    }
}
