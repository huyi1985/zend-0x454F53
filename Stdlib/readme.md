```php
$data = array(
    array(3, 4, true, false),
    array(4, 5, false, false),
    array(4, 5, false, false),
    array(5, 6, false, true),
);

$rangeSet = new Eos_Stdlib_RangeSet();
foreach ($data as $_range) {
    $range = new Eos_Stdlib_Range($_range[0], $_range[1], $_range[2], $_range[3]);
    $rangeSet->addRange($range);
}

$ranges = $rangeSet->getRanges();
foreach ($ranges as $_i => $_range) {
    echo $_i, ': ', $_range, PHP_EOL;
}

$x = $this->_getParam('x', (mt_rand(3, 6) + mt_rand(1, 100) / 100));
try {
    $range = $rangeSet->search($x);
    echo $x, " in ", $range, PHP_EOL;
} catch (Exception $e) {
    echo $e->getMessage(), PHP_EOL;
}
```
