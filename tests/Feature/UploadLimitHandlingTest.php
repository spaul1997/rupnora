<?php

namespace Tests\Feature;

use Tests\TestCase;

class UploadLimitHandlingTest extends TestCase
{
    public function test_oversized_posts_show_a_clear_upload_limit_message(): void
    {
        $this->withServerVariables([
            'CONTENT_LENGTH' => 50 * 1024 * 1024,
        ])
            ->post('/admin/products')
            ->assertStatus(413)
            ->assertSee('The upload is larger than the server allows')
            ->assertSee('post_max_size')
            ->assertSee('upload_max_filesize');
    }
}
