#!/usr/bin/env bash

for i in {1..10}; do
    docker compose run --rm -it php env php test.php
done

# Without container cache:
# Time taken: 593.79887580872 ms
# Time taken: 636.85989379883 ms
# Time taken: 644.72985267639 ms
# Time taken: 616.33396148682 ms
# Time taken: 625.52714347839 ms
# Time taken: 629.25004959106 ms
# Time taken: 613.81006240845 ms
# Time taken: 623.01516532898 ms
# Time taken: 642.65584945679 ms
# Time taken: 620.72491645813 ms
# Average: 624.591 ms


