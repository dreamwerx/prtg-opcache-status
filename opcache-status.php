<?php

class opcacheStatus {

  const cacheKey = 'PRTG_OPCACHE_DATA';

  private $cacheData;
  private $smaData;

  public function getJson() {
    if (!function_exists('apcu_cache_info')) {
      return json_encode(['prtg' => ['error' => 1], ['text' => 'APC does not appear to be loaded']]);
    }

    $this->cacheData = apcu_cache_info();
    $this->smaData = apcu_sma_info();

    return json_encode($this->getResponse());
  }

  private function getResponse() {
    return [
      'prtg' => [
        'result' => $this->getChannelData()
      ]
    ];
  }

  private function getChannelData() {
    $tsUptime = time() - $this->cacheData['start_time'];

    $previousRequestTimestamp = $tsUptime;
    $previousRequestHit = 0;
    $previousRequestMiss = 0;
    $previousRequestInsert = 0;

    // Update cache with hit data for realtime numbers not averages
    $result = false;
    $metricData = apc_fetch(opcacheStatus::cacheKey, $result);
    if ($result !== false) {
      $previousRequestTimestamp = $metricData['previousRequestTimestamp'];
      $previousRequestHit = $metricData['previousRequestHit'];
      $previousRequestMiss = $metricData['previousRequestMiss'];
      $previousRequestInsert = $metricData['previousRequestInsert'];
    }

    // Calc difference
    $intervalSize = time() - $previousRequestTimestamp;
    $currentRequestRate = 0;
    $currentRequestHit = 0;
    $currentRequestMiss = 0;
    $currentRequestInsert = 0;

    if ($intervalSize > 0) {

      $currentRequestRate = ( ($this->cacheData['num_hits'] + $this->cacheData['num_misses']) - ($previousRequestHit + $previousRequestMiss) ) / $intervalSize;
      $currentRequestHit = ($this->cacheData['num_hits'] - $previousRequestHit) / $intervalSize;
      $currentRequestMiss = ($this->cacheData['num_misses'] - $previousRequestMiss) / $intervalSize;
      $currentRequestInsert = ($this->cacheData['num_inserts'] - $previousRequestInsert) / $intervalSize;
    }

    // Update cache
    $metricData = $this->getMetricDataForCache();
    apc_store(opcacheStatus::cacheKey, $metricData, 0);

    return [
      [
        'channel' => 'Memory Limit',
        'value' => $this->smaData['seg_size'] * $this->smaData['num_seg'],
        'unit' => 'BytesDisk'
      ],
      [
        'channel' => 'Memory Free',
        'value' => $this->smaData['avail_mem'],
        'unit' => 'BytesDisk',
        'limitmaxerror' => ceil($this->smaData['avail_mem'] * 0.9),
        'limitmaxwarning' => ceil($this->smaData['avail_mem'] * 0.7)
      ],
      [
        'channel' => 'Memory Used',
        'value' => ($this->smaData['seg_size'] * $this->smaData['num_seg']) - $this->smaData['avail_mem'],
        'unit' => 'BytesDisk'
      ],
      [
        'channel' => 'Cache Entries',
        'value' => $this->cacheData['num_entries']
      ],
      [
        'channel' => 'Average Request Rate /s',
        'value' => sprintf('%.2f', $this->cacheData['num_hits'] ? (($this->cacheData['num_hits'] + $this->cacheData['num_misses']) / $tsUptime) : 0),
        'float' => 1
      ],
      [
        'channel' => 'Average Hit Rate /s',
        'value' => sprintf('%.2f', $this->cacheData['num_hits'] ? (($this->cacheData['num_hits']) / $tsUptime) : 0),
        'float' => 1
      ],
      [
        'channel' => 'Average Miss Rate /s',
        'value' => sprintf('%.2f', $this->cacheData['num_misses'] ? (($this->cacheData['num_misses']) / $tsUptime) : 0),
        'float' => 1
      ],
      [
        'channel' => 'Average Insert Rate /s',
        'value' => sprintf('%.2f', $this->cacheData['num_inserts'] ? (($this->cacheData['num_inserts']) / $tsUptime) : 0),
        'float' => 1
      ],
      [
        'channel' => 'Current Request Rate /s',
        'value' => sprintf('%.2f', $currentRequestRate),
        'float' => 1
      ],
      [
        'channel' => 'Current Hit Rate /s',
        'value' => sprintf('%.2f', $currentRequestHit),
        'float' => 1
      ],
      [
        'channel' => 'Current Miss Rate /s',
        'value' => sprintf('%.2f', $currentRequestMiss),
        'float' => 1
      ],
      [
        'channel' => 'Current Insert Rate /s',
        'value' => sprintf('%.2f', $currentRequestInsert),
        'float' => 1
      ],
    ];
  }

  private function getMetricDataForCache() : array {
    return [
      'previousRequestTimestamp' => time(),
      'previousRequestHit' => $this->cacheData['num_hits'],
      'previousRequestMiss' => $this->cacheData['num_misses'],
      'previousRequestInsert' => $this->cacheData['num_inserts']
    ];
  }

}

$status = new opcacheStatus();
echo $status->getJson();

