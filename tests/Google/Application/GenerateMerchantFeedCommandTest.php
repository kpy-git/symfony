<?php

declare(strict_types=1);

namespace App\Tests\Google\Application;

use App\Google\Service\GoogleMerchantFeedHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GenerateMerchantFeedCommandTest extends KernelTestCase
{
    public function testRunning(): void
    {
        $this->assertTrue(true);
    }


}
