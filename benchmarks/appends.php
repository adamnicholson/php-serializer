<?php

require __DIR__ . '/../vendor/autoload.php';

$limitAppends = 2000;
$controlSize = 10000;
function getControlData()
{
    global $controlSize;
    $a = [];
    for ($i=0; $i<=$controlSize; $i++) {
        $a[] = $i;
    }
    return serialize($a);
}

echo "Building control data\n";

// Oldskool PHP
echo "Benchmarking appends by unserialize(), array_push(), and serialize()\n";

$bench = new Ubench;
$bench->start();

$data = getControlData();
for ($i=0; $i<=$limitAppends; $i++) {
    $un = unserialize($data);
    array_push($un, $i);
    $data = serialize($un);
}

echo "- Counting items in array: " . count(unserialize($data)) . " items\n";
$bench->end();
echo "- Performed " . $limitAppends . " appends to a data set of " . $controlSize . " in " . $bench->getTime() . ", with a memory peack of " . $bench->getMemoryPeak() . "\n";


// PHPSerializer
echo "Benchmarking appends PHPSerializer\\SerializeArray::append()\n";

$bench = new Ubench;
$bench->start();

$array = \PHPSerializer\SerializedArray::createFromString(getControlData());

for ($i=0; $i<=$limitAppends; $i++) {
    $array->append($i);
}

echo "- Counting items in array: " . $array->count() . " items\n";
$bench->end();
echo "- Performed " . $limitAppends . " appends to a data set of " . $controlSize . " in " . $bench->getTime() . ", with a memory peack of " . $bench->getMemoryPeak() . "\n";
