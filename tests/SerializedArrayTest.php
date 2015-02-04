<?php

namespace PHPSerializer;

use SplFileObject;
use SplTempFileObject;
use PHPUnit_Framework_Testcase;

class SerializedArrayTest extends PHPUnit_Framework_Testcase
{
    public function testInstance()
    {
        $stream = new SplFileObject(__DIR__ . '/data/simple-array-of-scalars.serialized', 'r');
        $array = new SerializedArray($stream);
        $this->assertTrue($array instanceof SerializedArray);
    }

    public function testInstanceWithInMemoryArray()
    {
        $stream = new SplTempFileObject();
        $stream->fwrite(serialize(['First Item', 2, true, 15.25]));
        $array = new SerializedArray($stream);
        $this->assertTrue($array instanceof SerializedArray);
    }

    public function testCreateFromString()
    {
        $array = SerializedArray::createFromString(serialize(['foo', 'bar']));
        $this->assertTrue($array instanceof SerializedArray);
    }

    public function testCreateFromArray()
    {
        $array = SerializedArray::createFromArray(['foo', 'bar']);
        $this->assertTrue($array instanceof SerializedArray);
    }

    public function testInstanceWithFlatAssociativeArray()
    {
        $array = SerializedArray::createFromArray(['foo' => 'bar', 'baz' => 'boz']);

        $this->assertTrue($array instanceof SerializedArray);

        $array->next();
        $this->assertEquals($array->key(), 'foo');
        $this->assertEquals($array->current(), 'bar');


        $array->next();
        $this->assertTrue($array->valid());
        $this->assertEquals($array->key(), 'baz');
        $this->assertEquals($array->current(), 'boz');

        $array->next();
        $this->assertFalse($array->valid());
    }

    public function testInstanceWithFlatIndexedArray()
    {
        $array = SerializedArray::createFromArray(['bar','boz']);

        $this->assertTrue($array instanceof SerializedArray);

        $array->next();
        $this->assertEquals($array->current(), 'bar');

        $array->next();
        $this->assertTrue($array->valid());
        $this->assertEquals($array->current(), 'boz');

        $array->next();
        $this->assertFalse($array->valid());
    }

    public function testFirstCallToNextReturnsFirstItem()
    {
        $stream = new SplTempFileObject();
        $stream->fwrite(serialize(['First Item', 2, true, 15.25]));
        $array = new SerializedArray($stream);
        $array->next();
        $this->assertEquals($array->key(), 0);
        $this->assertEquals($array->current(), 'First Item');
    }

    public function testValidTrueDuringItemsAndFalseAtEndOfArray()
    {
        $stream = new SplTempFileObject();
        $stream->fwrite(serialize(['First Item', 'There are only 2 items']));
        $array = new SerializedArray($stream);

        $array->next();
        $this->assertTrue($array->valid());

        $array->next();
        $this->assertTrue($array->valid());

        $array->next();
        $this->assertFalse($array->valid());
    }

    public function testArrayCanBeRebuilt()
    {
        $rawArray = ['foo', 'bar'];
        $array = SerializedArray::createFromArray($rawArray);
        $rebuiltArray = [];
        foreach ($array as $key => $value) {
            $rebuiltArray[$key] = $value;
        }
        $this->assertEquals($rawArray, $rebuiltArray);
    }

    public function testCountReturnsExpected()
    {
        $rawArray = ['foo', 'bar', 'foo', 5, 19.2, true, false, []];
        $array = SerializedArray::createFromArray($rawArray);
        $this->assertEquals($array->count(), 8);
    }
}
