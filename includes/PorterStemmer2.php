<?php

/**
 * PHP5 port of Kamil Bartocha C# implementation of the English (Porter2) stemming algorithm
 *
 * @author Goran Miskovic
 * @license The MIT License (MIT)
 * @package Porter Stemmer
 * @Since 1.0
 * 
 *
Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/**
 * Builds a standard string from the given word
 */

class StringBuilder
{
    private $capacity = 0;
    private $length = 0;
    private $replacement = '';
    private $string = null;
    private $temp_self = null;
    /**
     * @var \ArrayIterator
     */
    private $temp_string = null;

    /**
     * Class constructor
     *
     * @param string $string
     * @param integer $capacity
     */
    public function __construct($string = '', $capacity = 0)
    {
        $this->string = $string;
    }

    public function __toString()
    {
        return $this->string;
    }

    public function append($string)
    {
        $this->string .= $string;
    }

    public function AppendFormat()
    {
        //@TODO: yet to be implemented;
    }

    public function count()
    {
        return strlen($this->string);
    }


    public function EnsureCapacity()
    {
        //@TODO: check if there is any point to implement this method in PHP;
    }

    public function Equals(StringBuilder $instance)
    {
        if ((string)$instance === (string)$this) :
            return true;
        else :
            return false;
        endif;
    }

    public function GetHashCode()
    {
        //@TODO: Consider if there is meaningful PHP implementation;
    }

    //@TODO: Reconsider if implementation is meaningful
    public function getType()
    {
        return get_class();
    }

    public function Insert($index, $value, $start = 0, $length = null)
    {
        if ($index >= $this->count())
            throw new \Exception('Out of range!', 101);
        $this->string = (($index === 0) ? '' : substr($this->string, 0, $index)) .
            $value . substr($this->string, $index);
    }

    public function offsetGet($offset)
    {
        return substr($this->string, $offset, 1);
    }

    public function offsetSet($offset, $value)
    {
        if ($offset >= $this->count())
            throw new \Exception('Out of range!', 101);

        $this->string = substr($this->string, 0, $offset) .
            $value .
            substr($this->string, $offset + 1);
    }

    public function offsetUnset($offset)
    {
        $this->Remove($offset);
    }


    public function Remove($index, $length = 1)
    {
        if (($index + $length) > ($this->count())) :
            throw new \Exception('Out of range!', 101);
        endif;

        $this->string = (($index === 0) ? '' : substr($this->string, 0, $index)) .
            ((($index + $length) < $this->count()) ?
                substr($this->string, $index + $length) : '');
    }

    public function Replace($search, $replace, $start = 0, $length = null)
    {
        if ((0 === $start) && (null === $length) && (false !== strpos($this->string, $search))) :
            $this->string = substr($this->string, strpos($this->string, $search)) . $replace . substr($this->string, strpos($this->string, $search) + 1); elseif (false !== strpos($this->string, $search, $start)) :
            $this->string = substr($this->string, 0, $start) .
                str_replace($search, $replace, substr($this->string, $start, $length)) .
                substr($this->string, $start + $length);
        endif;

    }

    /**
     * Replace portion of the string
     *
     * @deprecated
     *
     * @param integer $index
     * @param string $value
     * @param integer $start
     * @param integer $length
     *
     * @access public
     * @return void
     */
    public function ReplaceChunk($index, $value, $start = 0, $length = null)
    {
        if (null !== $length) :
            $this->length = $length;
        endif;

        switch (true) :
            case (is_string($value)) :
                $this->temp_string = new \ArrayIterator(str_split($value));
                if (null === $length)
                    $this->length = strlen($value);
                break;
            case ($value instanceof \ArrayIterator) :
                $this->temp_string = $value;
                if (null === $length)
                    $this->length = $value->count();
                break;
            case (is_array($value)) :
                $this->temp_string = new \ArrayIterator($value);
                if (null === $length)
                    $this->length = count($value);
                break;
            default:
                throw new \Exception('Invalid data type', 101);
        endswitch;

        $this->temp_string->seek($start);
        for ($i = $start; $i < $this->length; $i++) :
            $this->offsetSet($index, $this->temp_string->current());
            $index++;
            $this->temp_string->next();
        endfor;
        $this->temp_string = null;
    }

    public function SubStr($start, $length)
    {
        return substr($this->string, $start, $length);
    }

}

/**
 * Main class used to stem a word
 */

class PorterStemmer2
{

    private $doubles = array("bb", "dd", "ff", "gg", "mm", "nn", "pp", "rr", "tt");
    private $exceptions = null;
    private $exceptions2 = null;
    private $found = false;
    private $r1 = 0;
    private $r2 = 0;
    /**
     * @var StringBuilder
     */
    private $sb = null;
    private $step1bReplacements = null;
    private $step2Replacements = null;
    private $step3Replacements = null;
    private $step4Replacements = null;
    private $validEndings = array("c", "d", "e", "g", "h", "k", "m", "n", "r", "t");

    public function __construct()
    {
        //TODO: Should be done better. Cashing or/and xml file perhaps
        $this->exceptions = new \ArrayIterator(
            array("skis" => "ski",
                "skies" => "sky",
                "dying" => "die",
                "lying" => "lie",
                "tying" => "tie",
                "idly" => "idl",
                "gently" => "gentl",
                "ugly" => "ugli",
                "early" => "earli",
                "only" => "onli",
                "singly" => "singl",
                "sky" => "sky",
                "news" => "news",
                "howe" => "howe",
                "atlas" => "atlas",
                "cosmos" => "cosmos",
                "bias" => "bias",
                "andes" => "andes"));

        $this->exceptions2 = new \ArrayIterator(
            array("inning",
                "outing",
                "canning",
                "herring",
                "earring",
                "proceed",
                "exceed",
                "succeed"));

        $this->step1bReplacements = new \ArrayIterator(
            array(
                "eedly" => "ee",
                "ingly" => "",
                "edly" => "",
                "eed" => "ee",
                "ing" => "",
                "ed" => ""));

        $this->step2Replacements = new \ArrayIterator(
            array(
                "ization" => "ize",
                "iveness" => "ive",
                "fulness" => "ful",
                "ational" => "ate",
                "ousness" => "ous",
                "biliti" => "ble",
                "tional" => "tion",
                "lessli" => "less",
                "fulli" => "ful",
                "entli" => "ent",
                "ation" => "ate",
                "aliti" => "al",
                "iviti" => "ive",
                "ousli" => "ous",
                "alism" => "al",
                "abli" => "able",
                "anci" => "ance",
                "alli" => "al",
                "izer" => "ize",
                "enci" => "ence",
                "ator" => "ate",
                "bli" => "ble",
                "ogi" => "og",
                "li" => ""));

        $this->step3Replacements = new \ArrayIterator(
            array(
                "ational" => "ate",
                "tional" => "tion",
                "alize" => "al",
                "icate" => "ic",
                "iciti" => "ic",
                "ative" => "",
                "ical" => "ic",
                "ness" => "",
                "ful" => ""));

        $this->step4Replacements = new \ArrayIterator(
            array("ement",
                "ment",
                "able",
                "ible",
                "ance",
                "ence",
                "ate",
                "iti",
                "ion",
                "ize",
                "ive",
                "ous",
                "ant",
                "ism",
                "ent",
                "al",
                "er",
                "ic"));
    }

    private function changeY()
    {
        if ('y' === $this->sb->offsetGet(0))
            $this->sb->offsetSet(0, 'Y');

        for ($i = 1; $i < $this->sb->count(); $i++) :
            if (('y' === $this->sb->offsetGet($i)) && (true === $this->isVowel($i - 1)))
                $this->sb->offsetSet($i, 'Y');
        endfor;
    }

    private function computeR1R2()
    {
        $this->r1 = $this->sb->count();
        $this->r2 = $this->sb->count();

        if (($this->sb->count() >= 5) &&
            ((0 === strcmp("gener", $this->sb->SubStr(0, 5))) || (0 === strcmp("arsen", $this->sb->SubStr(0, 5))))
        )
            $this->r1 = 5;

        if (($this->sb->count() >= 6) &&
            (0 === strcmp("commun", $this->sb->SubStr(0, 6)))
        )
            $this->r1 = 6;
        /*
         *  If r1 has not been changed by exception words
         */
        if ($this->r1 === $this->sb->count()) :
            /*
             * Compute R1 according to the algorithm
             */
            for ($i = 1; $i < $this->sb->count(); $i++) :
                if ((false === $this->isVowel($i)) && (true === $this->isVowel($i - 1))) :
                    $this->r1 = $i + 1;
                    break;
                endif;
            endfor;
        endif;

        //@TODO: Check line bellow! Added to simulate r1 check
//    if ($this->r2 === $this->sb->count()) :
        for ($i = $this->r1 + 1; $i < $this->sb->count(); $i++) :
            if ((false === $this->isVowel($i)) && (true === $this->isVowel($i - 1))) :
                $this->r2 = $i + 1;
                break;
            endif;
        endfor;
//     endif;
    }

    private function isShortSyllable($offset)
    {
        if ((0 === $offset) && ($this->isVowel(0)) && (!$this->isVowel(1))) :
            return true; elseif ((($offset > 0) && ($offset < ($this->sb->count() - 1)))
            && $this->isVowel($offset)
            && !$this->isVowel($offset + 1)
            && ($this->sb->offsetGet($offset + 1) != 'w' &&
                $this->sb->offsetGet($offset + 1) != 'x' &&
                $this->sb->offsetGet($offset + 1) != 'Y')
            && !$this->isVowel($offset - 1)
        ) :
            return true; else :
            return false;
        endif;
    }

    private function isShortWord()
    {
        if (($this->r1 >= $this->sb->count()) &&
            ($this->isShortSyllable($this->sb->count() - 2))
        )
            return true;
        else
            return false;
    }

    private function isVowel($offset)
    {
        switch ($this->sb->offsetGet($offset)) :
            case 'a':
                break;
            case 'e':
                break;
            case 'i':
                break;
            case 'o':
                break;
            case 'u':
                break;
            case 'y':
                break;
            default:
                return false;
        endswitch;
        return true;
    }

    private function step0()
    {
        if (($this->sb->count() >= 3) &&
            ("'s'" === $this->sb->SubStr($this->sb->count() - 3, 3))
        ) :
            $this->sb->Remove($this->sb->count() - 3, 3); elseif (($this->sb->count() >= 2) &&
            ("'s" === $this->sb->SubStr($this->sb->count() - 2, 2))
        ) :
            $this->sb->Remove($this->sb->count() - 2, 2); elseif ('\'' === $this->sb->offsetGet($this->sb->count() - 1)) :
            $this->sb->Remove($this->sb->count() - 1, 1);
        endif;;
    }

    private function step1a()
    {
        if ((($this->sb->count() >= 4) &&
            ("sses" === $this->sb->SubStr($this->sb->count() - 4, 4)))
        ) :
            $this->sb->Replace("sses", "ss", $this->sb->count() - 4, 4); elseif (($this->sb->count() >= 3) &&
            (($this->sb->SubStr($this->sb->count() - 3, 3) == "ied") ||
                ($this->sb->SubStr($this->sb->count() - 3, 3) == "ies"))
        ) :
            $this->sb->Replace(
                $this->sb->SubStr(
                    $this->sb->count() - 3, 3),
                ($this->sb->count() > 4) ? 'i' : 'ie',
                $this->sb->count() - 3, 3); elseif (($this->sb->count() >= 2) && (
                ($this->sb->SubStr($this->sb->count() - 2, 2) == "us") ||
                ($this->sb->SubStr($this->sb->count() - 2, 2) == "ss"))
        ) :
            return; elseif (($this->sb->count() > 0) && ($this->sb->SubStr($this->sb->count() - 1, 1) == "s")) :
            for ($i = 0; $i < $this->sb->count() - 2; $i++) :
                if ($this->isVowel($i)) :
                    $this->sb->Remove($this->sb->count() - 1, 1);
                    break;
                endif;
            endfor;
        endif;
    }

    private function step1b()
    {
        $this->step1bReplacements->rewind();
        while ($this->step1bReplacements->valid()) :
            if (($this->sb->count() > strlen($this->step1bReplacements->key())) && (
                    $this->sb->SubStr(
                        $this->sb->count() - strlen($this->step1bReplacements->key()),
                        strlen($this->step1bReplacements->key())) == $this->step1bReplacements->key())
            ) :

                switch ($this->step1bReplacements->key()) :
                    case "eedly" :
                        if (($this->sb->count() -
                                strlen($this->step1bReplacements->key())) >= $this->r1
                        ) :
                            $this->sb->Replace($this->step1bReplacements->key(),
                                $this->step1bReplacements->current(),
                                $this->sb->count() - strlen($this->step1bReplacements->key()),
                                strlen($this->step1bReplacements->key()));
                        endif;
                        break;
                    case "eed" :
                        if (($this->sb->count() -
                                strlen($this->step1bReplacements->key())) >= $this->r1
                        ) :
                            $this->sb->Replace($this->step1bReplacements->key(),
                                $this->step1bReplacements->current(),
                                $this->sb->count() - strlen($this->step1bReplacements->key()),
                                strlen($this->step1bReplacements->key()));
                        endif;
                        break;
                    default:
                        if (true === $this->found)
                            $this->found = false;
                        for ($j = 0;
                             $j < ($this->sb->count() -
                                 strlen($this->step1bReplacements->key())); $j++) :
                            if ($this->isVowel($j)) :
                                $this->sb->Replace($this->step1bReplacements->key(),
                                    $this->step1bReplacements->current(),
                                    $this->sb->count() - strlen($this->step1bReplacements->key()),
                                    strlen($this->step1bReplacements->key()));
                                $this->found = true;
                                break;
                            endif;
                        endfor;

                        if (false === $this->found)
                            return;

                        switch ($this->sb->SubStr($this->sb->count() - 2, 2)) :
                            case "at" :
                                $this->sb->append("e");

                                return;
                            case "bl" :
                                $this->sb->append("e");

                                return;
                            case "iz" :
                                $this->sb->append("e");

                                return;
                        endswitch;

                        if (in_array($this->sb->SubStr($this->sb->count() - 2, 2), $this->doubles, true)) :
                            $this->sb->Remove($this->sb->count() - 1, 1);

                            return;
                        endif;

                        if ($this->isShortWord())
                            $this->sb->append("e");
                        break;
                endswitch;
                return;
            endif;
            $this->step1bReplacements->next();
        endwhile;
    }

    private function step1c()
    {
        if (($this->sb->count() > 0)
            && (($this->sb->offsetGet($this->sb->count() - 1) == 'y' ||
                $this->sb->offsetGet($this->sb->count() - 1) == 'Y'))
            && ($this->sb->count() > 2)
            && (!$this->isVowel($this->sb->count() - 2))
        )
            $this->sb->offsetSet($this->sb->count() - 1, 'i');
    }

    private function step2()
    {
        $this->step2Replacements->rewind();
        while ($this->step2Replacements->valid()) :
            if (($this->sb->count() >= strlen($this->step2Replacements->key()))
                && ($this->sb->SubStr($this->sb->count() -
                        strlen($this->step2Replacements->key()),
                        strlen($this->step2Replacements->key()))
                    == $this->step2Replacements->key())
            ) :
                if (($this->sb->count() - strlen($this->step2Replacements->key()))
                    >= $this->r1
                ) :
                    switch ($this->step2Replacements->key()) :
                        case "ogi" :
                            if (($this->sb->count() > 3) &&
                                ($this->sb->offsetGet($this->sb->count() - strlen($this->step2Replacements->key()) - 1)
                                    == 'l')
                            )
                                $this->sb->Replace(
                                    $this->step2Replacements->key(),
                                    $this->step2Replacements->current(),
                                    $this->sb->count() - strlen($this->step2Replacements->key()),
                                    strlen($this->step2Replacements->key()));

                            return;

                        case "li" :
                            if (($this->sb->count() > 1) &&
                                in_array($this->sb->SubStr(
                                    $this->sb->count() - 3, 1), $this->validEndings, true)
                            )
                                $this->sb->Remove($this->sb->count() - 2, 2);

                            return;
                        default:
                            $this->sb->Replace(
                                $this->step2Replacements->key(),
                                $this->step2Replacements->current(),
                                $this->sb->count() - strlen($this->step2Replacements->key()),
                                strlen($this->step2Replacements->key()));

                            return;
                    endswitch; else :
                    return;
                endif;
            endif;

            $this->step2Replacements->next();
        endwhile;;
    }

    private function step3()
    {
        $this->step3Replacements->rewind();
        while ($this->step3Replacements->valid()) :
            if (($this->sb->count() >= strlen($this->step3Replacements->key())) &&
                ($this->sb->SubStr(
                        $this->sb->count() - strlen($this->step3Replacements->key()),
                        strlen($this->step3Replacements->key())
                    ) == $this->step3Replacements->key())
            ) :
                if (($this->sb->count() - strlen($this->step3Replacements->key())) >= $this->r1) :
                    switch ($this->step3Replacements->key()) :
                        case "ative" :
                            if (($this->sb->count() - strlen($this->step3Replacements->key())) >= $this->r2)
                                $this->sb->Replace($this->step3Replacements->key(),
                                    $this->step3Replacements->current(),
                                    $this->sb->count() - strlen($this->step3Replacements->key()),
                                    strlen($this->step3Replacements->key()));

                            return;

                        default:
                            $this->sb->Replace($this->step3Replacements->key(),
                                $this->step3Replacements->current(),
                                $this->sb->count() - strlen($this->step3Replacements->key()),
                                strlen($this->step3Replacements->key()));

                            return;
                    endswitch; else :
                    return;
                endif;
            endif;
            $this->step3Replacements->next();
        endwhile;;
    }

    private function step4()
    {
        $this->step4Replacements->rewind();
        while ($this->step4Replacements->valid()) :
            if (
                ($this->sb->count() >= strlen($this->step4Replacements->current())) &&
                ($this->sb->SubStr($this->sb->count() -
                        strlen($this->step4Replacements->current()),
                        strlen($this->step4Replacements->current()))
                    == $this->step4Replacements->current())
            ) :
                if (($this->sb->count() - strlen($this->step4Replacements->current()))
                    >= $this->r2
                ) :
                    switch ($this->step4Replacements->current()) :
                        case "ion" :
                            if (($this->sb->count() > 3) &&
                                (($this->sb->offsetGet($this->sb->count() -
                                        strlen($this->step4Replacements->current()) - 1)
                                        == 's') ||
                                    ($this->sb->offsetGet($this->sb->count() -
                                        strlen($this->step4Replacements->current()) - 1)
                                        == 't'))
                            )
                                $this->sb->Remove($this->sb->count() -
                                    strlen($this->step4Replacements->current()),
                                    strlen($this->step4Replacements->current()));

                            return;

                        default:
                            $this->sb->Remove($this->sb->count() -
                                strlen($this->step4Replacements->current()),
                                strlen($this->step4Replacements->current()));

                            return;
                    endswitch; else :
                    return;
                endif;
            endif;
            $this->step4Replacements->next();
        endwhile;
    }

    private function step5()
    {
        if ($this->sb->count() > 0) :
            if (
                ($this->sb->offsetGet($this->sb->count() - 1) == 'e') &&
                (
                    (($this->sb->count() - 1) >= $this->r2) ||
                    ((($this->sb->count() - 1) >= $this->r1) &&
                        (false === $this->isShortSyllable($this->sb->count() - 3)))
                )
            ):
                $this->sb->Remove($this->sb->count() - 1, 1); elseif (($this->sb->offsetGet($this->sb->count() - 1) == 'l') &&
                (($this->sb->count() - 1) >= $this->r2) &&
                ($this->sb->offsetGet($this->sb->count() - 2) == 'l')
            ) :
                $this->sb->Remove($this->sb->count() - 1, 1);
            endif;
        endif;
    }

    public function stem($word)
    {
        if (!is_string($word)) :
            throw new \Exception('Parameter one must be a string!', 111);
        endif;

        if (strlen(\stripslashes($word)) < 3) :
            return $word;
        endif;

        $this->sb = new StringBuilder(\strtolower(\stripslashes($word)));

        if (0 === strcmp("'", $this->sb->offsetGet(0))) :
            $this->sb->Remove(0);
        endif;

        //FIXME: Exceceptions test should go before creating StringBuilder object
        $this->exceptions->rewind();
        while ($this->exceptions->valid()) :
            if (0 === strcmp($word, $this->exceptions->key())) :
                return $this->exceptions->current();
            endif;

            $this->exceptions->next();
        endwhile;

        $this->changeY();
        $this->computeR1R2();

        $this->step0();
        $this->step1a();

        $this->exceptions2->rewind();
        while ($this->exceptions2->valid()) :
            if (0 === strcmp($this->sb, $this->exceptions2->current())) :
                return $this->exceptions2->current();
            endif;

            $this->exceptions2->next();
        endwhile;

        $this->step1b();
        $this->step1c();
        $this->step2();
        $this->step3();
        $this->step4();
        $this->step5();

        return strtolower($this->sb);
    }
}

?>
