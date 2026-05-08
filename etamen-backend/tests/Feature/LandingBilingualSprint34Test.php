<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingBilingualSprint34Test extends TestCase
{
    public function test_landing_page_defaults_to_arabic_rtl(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false)
            ->assertSee('ابحث عن طبيب')
            ->assertSee('English');
    }

    public function test_landing_page_supports_english_ltr(): void
    {
        $response = $this->get('/?lang=en');

        $response
            ->assertOk()
            ->assertSee('lang="en"', false)
            ->assertSee('dir="ltr"', false)
            ->assertSee('Find A Doctor!')
            ->assertSee('العربية');
    }
}
