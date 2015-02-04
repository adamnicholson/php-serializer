<?php

namespace PHPSerializer;

use SplFileObject;

class SerializedArray implements \Iterator, \Countable
{
    protected $file;
    protected $itemsCount;
    protected $current;
    protected $currentKey;
    protected $end = false;
    protected $itterated = 0;

    public function __construct(SplFileObject $file)
    {
        $this->file = $file;

        $this->rewind();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        if ($this->itterated === 0) {
            $this->next();
        }
        return $this->current;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $file = $this->file;

        // Move past the declaration
        if (($char = $file->fgetc()) !== ($exp = 'i') || ($char = $file->fgetc()) !== ($exp = ':')) {
            // The next characters aren't an array index identifier. Must be end of file.
            return $this->end = true;
        }
        $key = '';
        while (($char = $file->fgetc()) !== ';') {
            $key .= $char;
        }
        $this->currentKey = $key;

        $buildString = '';
        $stop = false;
        $levels = 0;
        while ($stop === false) {
            $char = $file->fgetc();

            if ($levels === 0 && $char === ';') {
                $stop = true;
            }

            if ($char === '{') {
                $levels++;
            }

            if ($levels === 1 && $char === '}') {
                $stop = true;
            }

            if ($char === '}') {
                $levels--;
            }

            $buildString .= $char;

        }

        $this->current = unserialize($buildString);
        $this->itterated++;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->currentKey;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->end === false;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $file = $this->file;

        // Move past the decleration
        $file->rewind();
        if (($char = $file->fgetc()) !== ($exp = 'a') || ($char = $file->fgetc()) !== ($exp = ':')) {
            throw new \Exception('Stream contents is corrupt');
        }

        // Move past the array count and remember it
        $index = '';
        while (($char = $file->fgetc()) !== ':') {
            $index .= $char;
        }
        $this->itemsCount = $index;

        // Move into the array
        if ($file->fgetc() !== '{') {
            throw new \Exception('Stream contents is corrupt');
        }

        $this->itterated = 0;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return $this->itemsCount;
    }


    public static function createFromArray(array $array)
    {
        return self::createFromString(serialize($array));
    }

    public static function createFromString($serializedString)
    {
        $file = new \SplTempFileObject();
        $file->fwrite($serializedString);
        return new SerializedArray($file);
    }
}
