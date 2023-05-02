<?php

namespace App\Message;

class ScanMessage
{
    public function __construct(private readonly string $jobId)
    {
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }
}