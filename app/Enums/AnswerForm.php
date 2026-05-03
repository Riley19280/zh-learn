<?php

namespace App\Enums;

enum AnswerForm: string {
    case Chinese = 'chinese';
    case Pinyin = 'pinyin';
    case English = 'english';

    public function label(): string {
        return match ($this) {
            self::Chinese => 'Chinese',
            self::Pinyin => 'Pinyin',
            self::English => 'English',
        };
    }
}
