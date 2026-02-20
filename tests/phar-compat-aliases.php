<?php

if (
    class_exists('PHPUnitPHAR\\SebastianBergmann\\Comparator\\ComparisonFailure')
    && ! class_exists('SebastianBergmann\\Comparator\\ComparisonFailure')
) {
    class_alias(
        'PHPUnitPHAR\\SebastianBergmann\\Comparator\\ComparisonFailure',
        'SebastianBergmann\\Comparator\\ComparisonFailure',
    );
}
