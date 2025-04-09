<?php
// Redis setup
$redis = new Redis();
try {
    $redis->connect('redis', 6379);
} catch (Exception $e) {
    error_log("Redis connection failed: " . $e->getMessage());
    $redis = null;
}

// Get cache
function cache_get($key) {
    global $redis;
    return $redis && $redis->exists($key) ? $redis->get($key) : false;
}

// Set cache with expiration time
// $ttl is in seconds, default is 60 seconds
function cache_set($key, $value, $ttl = 60) {
    global $redis;
    if ($redis) {
        $redis->setex($key, $ttl, $value);
    }
}

// Cache delete
function clear_cache($pattern = 'recipes:*') {
    global $redis;
    if ($redis) {
        foreach ($redis->keys($pattern) as $key) {
            $redis->del($key);
        }
    }
}
