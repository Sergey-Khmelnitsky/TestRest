<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Тесты, inspired by DeepSeek stack-model cases.
 * Ожидания адаптированы под наше ТЗ: whitelist (a, code, i, strike, strong), строгий XHTML.
 */
class DeepSeekInspiredValidatorTest extends TestCase
{
    #[DataProvider('deepSeekCasesProvider')]
    public function test_deepseek_inspired_case(string $post, bool $expected, string $note): void
    {
        $this->assertSame($expected, validatePost($post), $note);
    }

    /**
     * @return array<string, array{string, bool, string}>
     */
    public static function deepSeekCasesProvider(): array
    {
        $nestedStrong = static fn (int $depth): string => str_repeat('<strong>', $depth).str_repeat('</strong>', $depth);

        return [
            // 1. Базовые корректные случаи
            'ds01 empty string' => [
                '',
                false,
                'DeepSeek #1: пустая строка — у DeepSeek valid, у нас invalid по ТЗ',
            ],
            'ds02 plain text' => [
                'просто текст без тегов',
                true,
                'DeepSeek #2: текст без HTML',
            ],
            'ds03 simple b tag' => [
                '<b>жирный</b>',
                false,
                'DeepSeek #3: valid — у нас invalid, тег b не в whitelist',
            ],
            'ds03adapted simple strong tag' => [
                '<strong>жирный</strong>',
                true,
                'DeepSeek #3 (adapted): простейшая пара на strong',
            ],
            'ds04 nested b and i' => [
                '<b><i>жирный курсив</i></b>',
                false,
                'DeepSeek #4: valid — у нас invalid из-за b',
            ],
            'ds04adapted nested strong and i' => [
                '<strong><i>жирный курсив</i></strong>',
                true,
                'DeepSeek #4 (adapted): корректная вложенность',
            ],
            'ds05 sibling div and p' => [
                '<div><p>абзац</p><p>ещё</p></div>',
                false,
                'DeepSeek #5: valid — у нас invalid, div/p не в whitelist',
            ],
            'ds05adapted sibling strong tags' => [
                '<strong><i>абзац</i></strong><strong><i>ещё</i></strong>',
                true,
                'DeepSeek #5 (adapted): несколько пар на одном уровне',
            ],
            'ds06 void tags only' => [
                '<br><img src=\'x.jpg\'><hr>',
                false,
                'DeepSeek #6: valid void-теги — у нас invalid, void/br/img/hr не разрешены',
            ],

            // 2. Базовые ошибки
            'ds07 unclosed b' => [
                '<b>жирный',
                false,
                'DeepSeek #7: незакрытый b',
            ],
            'ds07adapted unclosed strong' => [
                '<strong>жирный',
                false,
                'DeepSeek #7 (adapted): незакрытый strong',
            ],
            'ds08 closing b without opening' => [
                'жирный</b>',
                false,
                'DeepSeek #8: закрывающий b без открывающего',
            ],
            'ds08adapted closing strong without opening' => [
                'жирный</strong>',
                false,
                'DeepSeek #8 (adapted): закрывающий strong без открывающего',
            ],
            'ds09 cross nested b and i' => [
                '<b><i>текст</b></i>',
                false,
                'DeepSeek #9: пересечение b/i',
            ],
            'ds09adapted cross nested strong and i' => [
                '<strong><i>текст</strong></i>',
                false,
                'DeepSeek #9 (adapted): ожидался </i>, встречен </strong>',
            ],
            'ds10 cross nested with trailing unclosed' => [
                '<b><i>текст</b>',
                false,
                'DeepSeek #10: несоответствие + незакрытый i',
            ],
            'ds10adapted cross nested strong i trailing' => [
                '<strong><i>текст</strong>',
                false,
                'DeepSeek #10 (adapted): несоответствие + незакрытый i',
            ],

            // 3. Void-теги
            'ds11 multiple br' => [
                '<br><br><br>',
                false,
                'DeepSeek #11: valid void-теги — у нас invalid',
            ],
            'ds12 div with br' => [
                '<div><br></div>',
                false,
                'DeepSeek #12: valid — у нас invalid',
            ],
            'ds13 br with closing br' => [
                '<br></br>',
                false,
                'DeepSeek #13: ошибка </br> — у нас invalid (br не в whitelist)',
            ],
            'ds14 img with closing img' => [
                '<img></img>',
                false,
                'DeepSeek #14: ошибка </img> — у нас invalid',
            ],
            'ds15 input void' => [
                '<input type=\'text\'>',
                false,
                'DeepSeek #15: valid void input — у нас invalid',
            ],

            // 4. Повторяющиеся теги
            'ds16 triple nested div' => [
                '<div><div><div></div></div></div>',
                false,
                'DeepSeek #16: valid nested div — у нас invalid',
            ],
            'ds16adapted triple nested strong' => [
                '<strong><strong><strong></strong></strong></strong>',
                true,
                'DeepSeek #16 (adapted): вложенные strong',
            ],
            'ds17 mixed nested div blocks' => [
                '<div><div></div></div><div></div>',
                false,
                'DeepSeek #17: valid — у нас invalid',
            ],
            'ds17adapted mixed nested strong blocks' => [
                '<strong><strong></strong></strong><strong></strong>',
                true,
                'DeepSeek #17 (adapted): смежные блоки strong',
            ],
            'ds18 nested span siblings' => [
                '<span><span></span><span></span></span>',
                false,
                'DeepSeek #18: valid span — у нас invalid',
            ],

            // 5. Регистр тегов
            'ds19 mixed case b tags' => [
                '<B>жирный</b>',
                false,
                'DeepSeek #19: valid при lowercasing — у нас invalid (b + uppercase B)',
            ],
            'ds20 mixed case div and p' => [
                '<DiV><P>текст</P></DiV>',
                false,
                'DeepSeek #20: valid — у нас invalid (div/p + mixed case)',
            ],
            'ds21 mixed case cross nest' => [
                '<B><I>текст</B></I>',
                false,
                'DeepSeek #21: ошибка пересечения',
            ],
            'ds21adapted mixed case cross nest allowed tags' => [
                '<strong><i>текст</strong></i>',
                false,
                'DeepSeek #21 (adapted): пересечение на разрешённых тегах',
            ],

            // 6. Теги с атрибутами
            'ds22 anchor single quoted href' => [
                '<a href=\'https://example.com\'>ссылка</a>',
                false,
                'DeepSeek #22: valid — у нас invalid, только двойные кавычки',
            ],
            'ds22adapted anchor double quoted href' => [
                '<a href="https://example.com">ссылка</a>',
                true,
                'DeepSeek #22 (adapted): anchor с двойными кавычками',
            ],
            'ds23 div with many attributes' => [
                '<div class=\'x\' data-x=\'y\' id=\'z\'><p style=\'color:red\'>текст</p></div>',
                false,
                'DeepSeek #23: valid — у нас invalid',
            ],
            'ds24 attribute value contains tag-like text' => [
                '<div class=\'<b>опасно</b>\'></div>',
                false,
                'DeepSeek #24: зависит от regex — у нас invalid (div не в whitelist)',
            ],
            'ds25 anchor href with greater-than single quotes' => [
                '<a href=\'>\'>ссылка</a>',
                false,
                'DeepSeek #25: single quotes — у нас invalid',
            ],
            'ds25adapted anchor href with greater-than double quotes' => [
                '<a href=">">ссылка</a>',
                false,
                'DeepSeek #25 (adapted): > в href — текущая regex обрывает тег на первом >',
            ],

            // 7. Спецсимволы и границы тегов
            'ds26 comparison operators in text' => [
                '3 < 5 и 7 > 4',
                false,
                'DeepSeek #26: valid — у нас invalid, строгий XHTML запрещает голый <',
            ],
            'ds27 less-than inside b content' => [
                '<b>3 < 5</b>',
                false,
                'DeepSeek #27: valid — у нас invalid (b + голый <)',
            ],
            'ds27adapted less-than inside strong content' => [
                '<strong>3 < 5</strong>',
                false,
                'DeepSeek #27 (adapted): голый < внутри контента',
            ],
            'ds28 extra angle brackets around tag' => [
                '<<<b>>></b>',
                false,
                'DeepSeek #28: valid — у нас invalid (b + остаточный <)',
            ],
            'ds28adapted extra angle brackets around strong' => [
                '<<<strong>>></strong>',
                false,
                'DeepSeek #28 (adapted): остаточные << после удаления тегов',
            ],
            'ds29 empty angle brackets' => [
                '<> пустой тег </>',
                false,
                'DeepSeek #29: зависит от реализации — у нас invalid',
            ],

            // 8. Комментарии
            'ds30 comment with b inside' => [
                '<!-- <b> -->',
                false,
                'DeepSeek #30: valid если комментарии вырезать — мы не вырезаем, regex видит <b>',
            ],
            'ds31 b with comment containing closing tag' => [
                '<b><!-- </b> -->текст</b>',
                false,
                'DeepSeek #31: valid если комментарии вырезать — у нас invalid',
            ],
            'ds32 unclosed comment' => [
                '<!-- незакрытый комментарий',
                false,
                'DeepSeek #32: valid если всё комментарий — у нас invalid из-за <',
            ],

            // 9. Составные случаи
            'ds33 multiple errors in one string' => [
                '<b><i>текст</b><u></i></u>',
                false,
                'DeepSeek #33: несколько ошибок — у нас invalid',
            ],
            'ds33adapted multiple errors allowed tags' => [
                '<strong><i>текст</strong><strike></i></strike>',
                false,
                'DeepSeek #33 (adapted): несоответствие </strong> вместо </i>',
            ],
            'ds34 leading closings and mismatch' => [
                '</a><b></i>',
                false,
                'DeepSeek #34: </a> без открывающего, </i> без пары, b не закрыт',
            ],
            'ds34adapted leading closings allowed tags' => [
                '</a><strong></i>',
                false,
                'DeepSeek #34 (adapted): те же ошибки на разрешённых/смешанных тегах',
            ],

            // 10. Стресс-тесты для стека
            'ds35 deep nesting strong' => [
                $nestedStrong(1000),
                true,
                'DeepSeek #35 (adapted): 1000 вложенных strong',
            ],
            'ds36 complex nesting original tags' => [
                '<b>текст1<i>текст2<u>текст3</u><s>текст4</s></i></b>',
                false,
                'DeepSeek #36: valid — у нас invalid (b/u/s не в whitelist)',
            ],
            'ds36adapted complex nesting allowed tags' => [
                '<strong>текст1<i>текст2<strike>текст3</strike><strike>текст4</strike></i></strong>',
                true,
                'DeepSeek #36 (adapted): сложная корректная вложенность',
            ],
            'ds37 complex partial nesting original' => [
                '<b>1<i>2<u>3<s>4</s></u><s>5</s></i></b>',
                false,
                'DeepSeek #37: valid — у нас invalid',
            ],
            'ds37adapted cross nested strike and i' => [
                '<strong>1<i>2<strike>3</i></strike></strong>',
                false,
                'DeepSeek #37 (adapted): пересечение strike/i вместо вложенного strike',
            ],
            'ds37adapted2 complex nesting allowed tags' => [
                '<strong>1<i>2<strike>3</strike></i><strike>5</strike></strong>',
                true,
                'DeepSeek #37 (adapted v2): корректная структура на разрешённых тегах',
            ],
            'ds38 unknown nested tags' => [
                '<a><b><c><d><e><f></f></e></d></c></b></a>',
                false,
                'DeepSeek #38: valid для произвольных имён — у нас invalid (только whitelist)',
            ],

            // 11. Пограничные случаи с пробелами
            'ds39 space before closing bracket' => [
                '<b> текст </b >',
                false,
                'DeepSeek #39: valid с пробелом перед > — у нас invalid (b)',
            ],
            'ds39adapted space before closing bracket strong' => [
                '<strong> текст </strong >',
                true,
                'DeepSeek #39 (adapted): пробел перед > в </strong >',
            ],
            'ds40 space after opening bracket' => [
                '< b>не тег</b>',
                false,
                'DeepSeek #40: < b> не тег, </b> без пары',
            ],
            'ds41 newline inside tag name original' => [
                "<b\n>текст</b\n>",
                false,
                'DeepSeek #41: valid с переносом — у нас invalid (b)',
            ],
            'ds41adapted newline inside tag' => [
                "<strong\n>текст</strong\n>",
                true,
                'DeepSeek #41 (adapted): перенос строки внутри тега strong',
            ],
        ];
    }
}
